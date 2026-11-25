<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Plugin\Finder\Pmsnippets\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\QueryInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use PontoMega\Component\Pmsnippets\Site\Helper\RouteHelper;

final class Pmsnippets extends Adapter implements SubscriberInterface
{
    use DatabaseAwareTrait;

    protected $context = 'Pmsnippets';

    protected $extension = 'com_pmsnippets';

    protected $layout = 'snippet';

    protected $type_title = 'Joomla Snippet';

    protected $table = '#__pmsnippets_items';

    protected $state_field = 'state';

    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onFinderCategoryChangeState' => 'onFinderCategoryChangeState',
            'onFinderChangeState'         => 'onFinderChangeState',
            'onFinderAfterDelete'         => 'onFinderAfterDelete',
            'onFinderBeforeSave'          => 'onFinderBeforeSave',
            'onFinderAfterSave'           => 'onFinderAfterSave',
        ]);
    }

    protected function setup(): bool
    {
        return true;
    }

    public function onFinderCategoryChangeState(FinderEvent\AfterCategoryChangeStateEvent $event): void
    {
        if ($event->getExtension() === 'com_pmsnippets') {
            $this->categoryStateChange($event->getPks(), $event->getValue());
        }
    }

    public function onFinderAfterDelete(FinderEvent\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();

        if ($context === 'com_pmsnippets.snippet') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return;
        }

        $this->remove($id);
    }

    public function onFinderAfterSave(FinderEvent\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        if ($context === 'com_pmsnippets.snippet') {
            if (!$isNew && $this->old_access != $row->access) {
                $this->itemAccessChange($row);
            }

            $this->reindex($row->id);
        }

        if ($context === 'com_categories.category') {
            if (!$isNew && $this->old_cataccess != $row->access) {
                $this->categoryAccessChange($row);
            }
        }
    }

    public function onFinderBeforeSave(FinderEvent\BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        if ($context === 'com_pmsnippets.snippet' && !$isNew) {
            $this->checkItemAccess($row);
        }

        if ($context === 'com_categories.category' && !$isNew) {
            $this->checkCategoryAccess($row);
        }
    }

    public function onFinderChangeState(FinderEvent\AfterChangeStateEvent $event): void
    {
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();

        if ($context === 'com_pmsnippets.snippet') {
            $this->itemStateChange($pks, $value);
        }

        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    protected function index(Result $item): void
    {
        $item->setLanguage();

        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->context  = 'com_pmsnippets.snippet';
        $item->params   = clone ComponentHelper::getParams('com_pmsnippets', true);
        $item->metadata = new Registry($item->metadata);

        // Combine technical explanation and source code text into searchable body
        $codeText   = trim((string) ($item->code_search_text ?? ''));
        $descText   = trim(strip_tags((string) ($item->body ?? '')));
        $item->body = trim($descText . "\n\n" . $codeText);

        $item->url   = $this->getUrl($item->id, $this->extension, $this->layout);
        $item->route = RouteHelper::getSnippetRoute($item->slug, (int) $item->catid, (string) $item->language);

        $item->addInstruction(Indexer::META_CONTEXT, 'metakey');
        $item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(Indexer::META_CONTEXT, 'code_language');
        $item->addInstruction(Indexer::META_CONTEXT, 'context_type');

        $item->state = $this->translateState($item->state, $item->cat_state);

        $taxonomies = $this->params->get('taxonomies', ['type', 'category', 'version', 'code_language', 'context', 'language']);
        if (\is_string($taxonomies)) {
            $taxonomies = array_filter(array_map('trim', explode(',', $taxonomies)));
        }

        if (\in_array('type', $taxonomies, true)) {
            $item->addTaxonomy('Type', $this->type_title);
        }

        if (\in_array('category', $taxonomies, true) && !empty($item->catid)) {
            $categories = $this->getApplication()->bootComponent('com_pmsnippets')->getCategory(['published' => false, 'access' => false]);
            $category   = $categories ? $categories->get($item->catid) : null;
            if ($category) {
                $item->addNestedTaxonomy('Category', $category, $this->translateState($category->published), $category->access, $category->language);
            }
        }

        if (\in_array('version', $taxonomies, true) && !empty($item->versions_csv)) {
            $versionLabels = [
                'j6' => 'Joomla 6',
                'j5' => 'Joomla 5',
                'j4' => 'Joomla 4',
                'j3' => 'Joomla 3',
            ];
            foreach (array_filter(explode(',', (string) $item->versions_csv)) as $vCode) {
                $item->addTaxonomy('Joomla Version', $versionLabels[$vCode] ?? strtoupper($vCode));
            }
        }

        if (\in_array('code_language', $taxonomies, true) && !empty($item->code_language)) {
            $item->addTaxonomy('Code Language', strtoupper((string) $item->code_language));
        }

        if (\in_array('context', $taxonomies, true) && !empty($item->context_type)) {
            $item->addTaxonomy('Snippet Context', ucfirst((string) $item->context_type));
        }

        if (\in_array('language', $taxonomies, true)) {
            $item->addTaxonomy('Language', $item->language);
        }

        Helper::getContentExtras($item);

        $this->indexer->index($item);
    }

    protected function getListQuery($query = null): QueryInterface
    {
        $db = $this->getDatabase();

        $query = $query instanceof QueryInterface ? $query : $db->createQuery()
            ->select('a.id, a.title, a.alias, a.summary, a.description AS body, a.code_search_text')
            ->select('a.code_language, a.context_type, a.difficulty')
            ->select('a.state, a.catid, a.created AS start_date, a.created_by')
            ->select('a.modified, a.modified_by, a.metakey, a.metadesc, a.metadata, a.language, a.access, a.ordering')
            ->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
            ->select('c.title AS category, c.published AS cat_state, c.access AS cat_access')
            ->select('(SELECT GROUP_CONCAT(v.version_code ORDER BY v.version_code DESC SEPARATOR \',\') FROM #__pmsnippets_item_versions AS v WHERE v.snippet_id = a.id) AS versions_csv');

        $caseWhenAlias = ' CASE WHEN ' . $query->charLength('a.alias', '!=', '0') . ' THEN ';
        $aId           = $query->castAs('CHAR', 'a.id');
        $caseWhenAlias .= $query->concatenate([$aId, 'a.alias'], ':') . ' ELSE ' . $aId . ' END AS slug';
        $query->select($caseWhenAlias)
            ->from('#__pmsnippets_items AS a')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');

        return $query;
    }
}
