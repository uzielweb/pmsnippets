<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\ParameterType;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;

class SnippetModel extends ItemModel
{
    protected function populateState(): void
    {
        $app = Factory::getApplication();
        $id  = $app->getInput()->getInt('id', 0);

        $this->setState('snippet.id', $id);
        $this->setState('params', $app->getParams('com_pmsnippets'));
    }

    public function getItem($pk = null)
    {
        $pk = (int) ($pk ?: $this->getState('snippet.id'));

        if ($this->_item === null) {
            $this->_item = [];
        }

        if (!isset($this->_item[$pk])) {
            $db     = $this->getDatabase();
            $user   = Factory::getApplication()->getIdentity();
            $groups = $user ? $user->getAuthorisedViewLevels() : [1];

            $query = $db->getQuery(true)
                ->select([
                    'a.*',
                    $db->quoteName('c.title', 'category_title'),
                    $db->quoteName('c.alias', 'category_alias'),
                    $db->quoteName('u.name', 'author_name'),
                    '(SELECT GROUP_CONCAT(v.version_code ORDER BY v.version_code DESC SEPARATOR \',\') FROM ' . $db->quoteName('#__pmsnippets_item_versions', 'v') . ' WHERE v.snippet_id = a.id) AS versions_csv',
                ])
                ->from($db->quoteName('#__pmsnippets_items', 'a'))
                ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
                ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'))
                ->where($db->quoteName('a.id') . ' = :id')
                ->where($db->quoteName('a.state') . ' = 1')
                ->whereIn($db->quoteName('a.access'), $groups)
                ->bind(':id', $pk, ParameterType::INTEGER);

            $data = $db->setQuery($query)->loadObject();

            if (!$data) {
                throw new \RuntimeException(Text::_('COM_PMSNIPPETS_ERROR_NOT_FOUND'), 404);
            }

            $data->blocks   = PmsnippetsHelper::parseCodeBlocks($data->code_blocks ?? '');
            $data->versions = array_values(array_filter(explode(',', (string) ($data->versions_csv ?? ''))));
            $data->slug     = $data->id . ':' . $data->alias;

            $data->tags = new TagsHelper();
            $data->tags->getItemTags('com_pmsnippets.snippet', $data->id);

            $this->_item[$pk] = $data;
        }

        return $this->_item[$pk];
    }

    public function getRelated(object $item, int $limit = 3): array
    {
        $db     = $this->getDatabase();
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user ? $user->getAuthorisedViewLevels() : [1];
        $lang   = Multilanguage::isEnabled() ? Factory::getApplication()->getLanguage()->getTag() : '';
        $itemId = (int) $item->id;
        $catId  = (int) $item->catid;

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('a.id'),
                $db->quoteName('a.catid'),
                $db->quoteName('a.title'),
                $db->quoteName('a.alias'),
                $db->quoteName('a.summary'),
                $db->quoteName('a.code_language'),
                $db->quoteName('a.context_type'),
                $db->quoteName('a.language'),
                $db->quoteName('c.title', 'category_title'),
                '(SELECT GROUP_CONCAT(v.version_code ORDER BY v.version_code DESC SEPARATOR \',\') FROM ' . $db->quoteName('#__pmsnippets_item_versions', 'v') . ' WHERE v.snippet_id = a.id) AS versions_csv',
            ])
            ->from($db->quoteName('#__pmsnippets_items', 'a'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->where($db->quoteName('a.id') . ' != :itemId')
            ->where($db->quoteName('a.catid') . ' = :catId')
            ->whereIn($db->quoteName('a.access'), $groups)
            ->bind(':itemId', $itemId, ParameterType::INTEGER)
            ->bind(':catId', $catId, ParameterType::INTEGER)
            ->order($db->quoteName('a.copies_count') . ' DESC')
            ->order($db->quoteName('a.hits') . ' DESC');

        if ($lang !== '') {
            $query->whereIn($db->quoteName('a.language'), [$lang, '*'], ParameterType::STRING);
        }

        $rows = $db->setQuery($query, 0, $limit)->loadObjectList() ?: [];

        foreach ($rows as $row) {
            $row->versions = array_values(array_filter(explode(',', (string) ($row->versions_csv ?? ''))));
            $row->slug     = $row->id . ':' . $row->alias;
        }

        return $rows;
    }

    public function hit(int $pk = 0): void
    {
        $pk = $pk ?: (int) $this->getState('snippet.id');

        if ($pk <= 0) {
            return;
        }

        $db  = $this->getDatabase();
        $upd = $db->getQuery(true)
            ->update($db->quoteName('#__pmsnippets_items'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $pk, ParameterType::INTEGER);
        $db->setQuery($upd)->execute();
    }
}
