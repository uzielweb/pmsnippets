<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;

class SnippetTable extends Table implements TaggableTableInterface
{
    use TaggableTableTrait;

    protected $_supportNullValue = true;

    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_pmsnippets.snippet';
        parent::__construct('#__pmsnippets_items', 'id', $db, $dispatcher);
        $this->setColumnAlias('published', 'state');
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['code_blocks']) && \is_array($array['code_blocks'])) {
            $blocks               = PmsnippetsHelper::parseCodeBlocks($array['code_blocks']);
            $array['code_blocks'] = json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (isset($array['metadata']) && \is_array($array['metadata'])) {
            $registry          = new Registry($array['metadata']);
            $array['metadata'] = (string) $registry;
        }

        return parent::bind($array, $ignore);
    }

    public function check(): bool
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $this->title = trim((string) $this->title);

        if ($this->title === '') {
            $this->setError(Text::_('COM_PMSNIPPETS_ERROR_TITLE_REQUIRED'));

            return false;
        }

        if (trim((string) $this->alias) === '') {
            $this->alias = $this->title;
        }

        $this->alias = ApplicationHelper::stringURLSafe((string) $this->alias, (string) $this->language);

        if (trim(str_replace('-', '', $this->alias)) === '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        // Verify unique alias per language
        $table = new self($this->getDbo(), $this->getDispatcher());
        if ($table->load(['alias' => $this->alias, 'language' => $this->language]) && (int) $table->id !== (int) $this->id) {
            $this->alias .= '-' . Factory::getDate()->format('His');
        }

        $blocks                 = PmsnippetsHelper::parseCodeBlocks($this->code_blocks);
        $this->code_blocks      = json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->code_search_text = PmsnippetsHelper::buildSearchText($blocks);

        if (empty($this->code_language) && !empty($blocks[0]['code_language'])) {
            $this->code_language = $blocks[0]['code_language'];
        }

        $now  = Factory::getDate()->toSql();
        $user = Factory::getApplication()->getIdentity();

        if (!$this->id) {
            if (empty($this->created)) {
                $this->created = $now;
            }
            if (empty($this->created_by)) {
                $this->created_by = $user ? (int) $user->id : 0;
            }
            if (empty($this->ordering)) {
                $this->ordering = $this->getNextOrder('catid = ' . (int) $this->catid . ' AND state >= 0');
            }
        }

        $this->modified    = $now;
        $this->modified_by = $user ? (int) $user->id : 0;

        if ($this->publish_up === '') {
            $this->publish_up = null;
        }

        if ($this->publish_down === '') {
            $this->publish_down = null;
        }

        return true;
    }

    public function getTypeAlias(): string
    {
        return $this->typeAlias;
    }
}
