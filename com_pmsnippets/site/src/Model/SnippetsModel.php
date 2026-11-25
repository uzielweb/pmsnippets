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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;

class SnippetsModel extends ListModel
{
    protected function populateState($ordering = null, $direction = null): void
    {
        $app    = Factory::getApplication();
        $input  = $app->getInput();
        $params = $app->getParams('com_pmsnippets');

        $this->setState('params', $params);

        $defaultLimit = (int) $params->get('items_per_page', ComponentHelper::getParams('com_pmsnippets')->get('items_per_page', 12));
        $limit        = $input->getUint('limit', $defaultLimit);
        $limitstart   = $input->getUint('limitstart', 0);

        $this->setState('list.limit', max(1, min(60, $limit)));
        $this->setState('list.start', $limitstart);

        $this->setState('filter.q', trim($input->getString('q', '')));
        $this->setState('filter.catid', $input->getInt('catid', (int) $params->get('catid', 0)));
        $this->setState('filter.version', strtolower(trim($input->getCmd('version', ''))));
        $this->setState('filter.code_lang', strtolower(trim($input->getCmd('code_lang', ''))));
        $this->setState('filter.context', strtolower(trim($input->getCmd('context', ''))));
        $this->setState('filter.difficulty', strtolower(trim($input->getCmd('difficulty', ''))));
        $this->setState('filter.tag', $input->getInt('tag', 0));

        $defaultSort = (string) $params->get('default_sort', 'featured');
        $sort        = strtolower(trim($input->getCmd('sort', $defaultSort)));
        $allowedSort = ['relevance', 'featured', 'copies', 'hits', 'recent', 'alpha'];
        if (!\in_array($sort, $allowedSort, true)) {
            $sort = $defaultSort;
        }
        $this->setState('filter.sort', $sort);
        $this->setState('filter.language', Multilanguage::isEnabled() ? $app->getLanguage()->getTag() : '');
    }

    protected function getStoreId($id = ''): string
    {
        $id .= ':' . $this->getState('filter.q');
        $id .= ':' . $this->getState('filter.catid');
        $id .= ':' . $this->getState('filter.version');
        $id .= ':' . $this->getState('filter.code_lang');
        $id .= ':' . $this->getState('filter.context');
        $id .= ':' . $this->getState('filter.difficulty');
        $id .= ':' . $this->getState('filter.tag');
        $id .= ':' . $this->getState('filter.sort');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    protected function getListQuery(): QueryInterface
    {
        $db     = $this->getDatabase();
        $query  = $db->getQuery(true);
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user ? $user->getAuthorisedViewLevels() : [1];
        $now    = Factory::getDate()->toSql();

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.catid'),
            $db->quoteName('a.title'),
            $db->quoteName('a.alias'),
            $db->quoteName('a.summary'),
            $db->quoteName('a.code_language'),
            $db->quoteName('a.context_type'),
            $db->quoteName('a.difficulty'),
            $db->quoteName('a.code_blocks'),
            $db->quoteName('a.featured'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.copies_count'),
            $db->quoteName('a.useful_count'),
            $db->quoteName('a.language'),
            $db->quoteName('a.created'),
            $db->quoteName('a.modified'),
            $db->quoteName('c.title', 'category_title'),
            $db->quoteName('c.alias', 'category_alias'),
            '(SELECT GROUP_CONCAT(v.version_code ORDER BY v.version_code DESC SEPARATOR \',\') FROM ' . $db->quoteName('#__pmsnippets_item_versions', 'v') . ' WHERE v.snippet_id = a.id) AS versions_csv',
        ])
            ->from($db->quoteName('#__pmsnippets_items', 'a'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->whereIn($db->quoteName('a.access'), $groups)
            ->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= :nowUp)')
            ->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= :nowDown)')
            ->bind(':nowUp', $now, ParameterType::STRING)
            ->bind(':nowDown', $now, ParameterType::STRING);

        // Language filter
        $langTag = (string) $this->getState('filter.language');
        if ($langTag !== '') {
            $query->whereIn($db->quoteName('a.language'), [$langTag, '*'], ParameterType::STRING);
        }

        // Category filter
        $catid = (int) $this->getState('filter.catid');
        if ($catid > 0) {
            $query->where($db->quoteName('a.catid') . ' = :catid')
                ->bind(':catid', $catid, ParameterType::INTEGER);
        }

        // Joomla version filter (pivot table)
        $version = (string) $this->getState('filter.version');
        if ($version !== '') {
            $query->join('INNER', $db->quoteName('#__pmsnippets_item_versions', 'fv'), $db->quoteName('fv.snippet_id') . ' = ' . $db->quoteName('a.id'))
                ->where($db->quoteName('fv.version_code') . ' = :version')
                ->bind(':version', $version, ParameterType::STRING);
        }

        // Code language filter
        $codeLang = (string) $this->getState('filter.code_lang');
        if ($codeLang !== '') {
            $query->where($db->quoteName('a.code_language') . ' = :codeLang')
                ->bind(':codeLang', $codeLang, ParameterType::STRING);
        }

        // Context filter
        $context = (string) $this->getState('filter.context');
        if ($context !== '') {
            $query->where($db->quoteName('a.context_type') . ' = :context')
                ->bind(':context', $context, ParameterType::STRING);
        }

        // Difficulty filter
        $difficulty = (string) $this->getState('filter.difficulty');
        if ($difficulty !== '') {
            $query->where($db->quoteName('a.difficulty') . ' = :difficulty')
                ->bind(':difficulty', $difficulty, ParameterType::STRING);
        }

        // Tag filter
        $tagId = (int) $this->getState('filter.tag');
        if ($tagId > 0) {
            $query->join(
                'INNER',
                $db->quoteName('#__contentitem_tag_map', 'tm'),
                $db->quoteName('tm.content_item_id') . ' = ' . $db->quoteName('a.id')
                . ' AND ' . $db->quoteName('tm.type_alias') . ' = ' . $db->quote('com_pmsnippets.snippet')
            )
                ->where($db->quoteName('tm.tag_id') . ' = :tagId')
                ->bind(':tagId', $tagId, ParameterType::INTEGER);
        }

        // Powerful Search: FULLTEXT relevance + LIKE symbol match (e.g. $app, J3, pageclass_sfx, RouteHelper)
        $search      = (string) $this->getState('filter.q');
        $hasSearch   = ($search !== '');
        $sort        = (string) $this->getState('filter.sort');

        if ($hasSearch) {
            $like = '%' . $search . '%';
            $tokens = array_values(array_filter(preg_split('/\s+/', $search) ?: [], static fn (string $t): bool => mb_strlen($t) >= 2));

            $likeParts = [
                $db->quoteName('a.title') . ' LIKE :likeTitle',
                $db->quoteName('a.summary') . ' LIKE :likeSummary',
                $db->quoteName('a.description') . ' LIKE :likeDesc',
                $db->quoteName('a.code_search_text') . ' LIKE :likeCode',
            ];
            $query->bind(':likeTitle', $like, ParameterType::STRING)
                ->bind(':likeSummary', $like, ParameterType::STRING)
                ->bind(':likeDesc', $like, ParameterType::STRING)
                ->bind(':likeCode', $like, ParameterType::STRING);

            if (mb_strlen($search) >= 3) {
                $ftExpr = 'MATCH(' . $db->quoteName('a.title') . ', ' . $db->quoteName('a.summary') . ', ' . $db->quoteName('a.description') . ', ' . $db->quoteName('a.code_search_text') . ') AGAINST (:ftQuery IN NATURAL LANGUAGE MODE)';
                $query->select($ftExpr . ' AS ' . $db->quoteName('relevance_score'))
                    ->bind(':ftQuery', $search, ParameterType::STRING);
                $likeParts[] = $ftExpr . ' > 0';
            } else {
                $query->select('0 AS ' . $db->quoteName('relevance_score'));
            }

            // Multi-word AND token fallback
            if (\count($tokens) > 1) {
                $tokenConds = [];
                foreach ($tokens as $idx => $tok) {
                    $pName = ':tok' . $idx;
                    $tLike = '%' . $tok . '%';
                    $tokenConds[] = '(' . $db->quoteName('a.title') . ' LIKE ' . $pName
                        . ' OR ' . $db->quoteName('a.summary') . ' LIKE ' . $pName
                        . ' OR ' . $db->quoteName('a.code_search_text') . ' LIKE ' . $pName . ')';
                    $query->bind($pName, $tLike, ParameterType::STRING);
                }
                $likeParts[] = '(' . implode(' AND ', $tokenConds) . ')';
            }

            $query->where('(' . implode(' OR ', $likeParts) . ')');
        } else {
            $query->select('0 AS ' . $db->quoteName('relevance_score'));
        }

        // Ordering
        if ($hasSearch && ($sort === 'relevance' || $sort === 'featured')) {
            $query->order($db->quoteName('relevance_score') . ' DESC')
                ->order($db->quoteName('a.featured') . ' DESC')
                ->order($db->quoteName('a.copies_count') . ' DESC');
        } else {
            switch ($sort) {
                case 'copies':
                    $query->order($db->quoteName('a.copies_count') . ' DESC')
                        ->order($db->quoteName('a.hits') . ' DESC');
                    break;
                case 'hits':
                    $query->order($db->quoteName('a.hits') . ' DESC');
                    break;
                case 'recent':
                    $query->order($db->quoteName('a.modified') . ' DESC')
                        ->order($db->quoteName('a.id') . ' DESC');
                    break;
                case 'alpha':
                    $query->order($db->quoteName('a.title') . ' ASC');
                    break;
                default:
                    $query->order($db->quoteName('a.featured') . ' DESC')
                        ->order($db->quoteName('a.ordering') . ' ASC')
                        ->order($db->quoteName('a.id') . ' DESC');
                    break;
            }
        }

        return $query;
    }

    public function getItems(): array
    {
        $items = parent::getItems() ?: [];

        foreach ($items as $item) {
            $item->blocks   = PmsnippetsHelper::parseCodeBlocks($item->code_blocks ?? '');
            $item->versions = array_values(array_filter(explode(',', (string) ($item->versions_csv ?? ''))));
            $item->slug     = $item->id . ':' . $item->alias;
        }

        return $items;
    }

    /**
     * Returns dynamic facet counts for Category, Joomla Version, Code Language, and Context.
     */
    public function getFacets(): array
    {
        $db      = $this->getDatabase();
        $user    = Factory::getApplication()->getIdentity();
        $groups  = $user ? $user->getAuthorisedViewLevels() : [1];
        $langTag = (string) $this->getState('filter.language');

        $baseWhere = static function (QueryInterface $q) use ($db, $groups, $langTag): void {
            $q->where($db->quoteName('a.state') . ' = 1')
                ->whereIn($db->quoteName('a.access'), $groups);
            if ($langTag !== '') {
                $q->whereIn($db->quoteName('a.language'), [$langTag, '*'], ParameterType::STRING);
            }
        };

        // Categories with counts
        $qCat = $db->getQuery(true)
            ->select([$db->quoteName('c.id'), $db->quoteName('c.title'), $db->quoteName('c.alias'), 'COUNT(' . $db->quoteName('a.id') . ') AS qty'])
            ->from($db->quoteName('#__categories', 'c'))
            ->join('INNER', $db->quoteName('#__pmsnippets_items', 'a'), $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id'))
            ->where($db->quoteName('c.extension') . ' = ' . $db->quote('com_pmsnippets'))
            ->where($db->quoteName('c.published') . ' = 1')
            ->group([$db->quoteName('c.id'), $db->quoteName('c.title'), $db->quoteName('c.alias')])
            ->order($db->quoteName('c.lft') . ' ASC');
        $baseWhere($qCat);
        $categories = $db->setQuery($qCat)->loadObjectList() ?: [];

        // Versions with counts
        $qVer = $db->getQuery(true)
            ->select([$db->quoteName('v.version_code'), 'COUNT(DISTINCT ' . $db->quoteName('v.snippet_id') . ') AS qty'])
            ->from($db->quoteName('#__pmsnippets_item_versions', 'v'))
            ->join('INNER', $db->quoteName('#__pmsnippets_items', 'a'), $db->quoteName('a.id') . ' = ' . $db->quoteName('v.snippet_id'))
            ->group($db->quoteName('v.version_code'));
        $baseWhere($qVer);
        $verRows  = $db->setQuery($qVer)->loadAssocList('version_code', 'qty') ?: [];
        $versions = [];
        foreach (PmsnippetsHelper::getVersionOptions() as $code => $label) {
            if (!empty($verRows[$code])) {
                $versions[] = (object) ['code' => $code, 'label' => $label, 'qty' => (int) $verRows[$code]];
            }
        }

        // Code languages with counts
        $qLang = $db->getQuery(true)
            ->select([$db->quoteName('a.code_language'), 'COUNT(' . $db->quoteName('a.id') . ') AS qty'])
            ->from($db->quoteName('#__pmsnippets_items', 'a'))
            ->group($db->quoteName('a.code_language'))
            ->order('qty DESC');
        $baseWhere($qLang);
        $langRows  = $db->setQuery($qLang)->loadAssocList('code_language', 'qty') ?: [];
        $codeLangs = [];
        $langOpts  = PmsnippetsHelper::getCodeLanguageOptions();
        foreach ($langRows as $code => $qty) {
            $codeLangs[] = (object) ['code' => $code, 'label' => $langOpts[$code] ?? strtoupper($code), 'qty' => (int) $qty];
        }

        // Contexts with counts
        $qCtx = $db->getQuery(true)
            ->select([$db->quoteName('a.context_type'), 'COUNT(' . $db->quoteName('a.id') . ') AS qty'])
            ->from($db->quoteName('#__pmsnippets_items', 'a'))
            ->group($db->quoteName('a.context_type'))
            ->order('qty DESC');
        $baseWhere($qCtx);
        $ctxRows  = $db->setQuery($qCtx)->loadAssocList('context_type', 'qty') ?: [];
        $contexts = [];
        $ctxOpts  = PmsnippetsHelper::getContextOptions();
        foreach ($ctxRows as $code => $qty) {
            $contexts[] = (object) ['code' => $code, 'label' => $ctxOpts[$code] ?? ucfirst($code), 'qty' => (int) $qty];
        }

        // Total library stats
        $qStats = $db->getQuery(true)
            ->select([
                'COUNT(' . $db->quoteName('a.id') . ') AS total_items',
                'COALESCE(SUM(' . $db->quoteName('a.copies_count') . '), 0) AS total_copies',
                'COALESCE(SUM(' . $db->quoteName('a.hits') . '), 0) AS total_hits',
            ])
            ->from($db->quoteName('#__pmsnippets_items', 'a'));
        $baseWhere($qStats);
        $stats = $db->setQuery($qStats)->loadObject() ?: (object) ['total_items' => 0, 'total_copies' => 0, 'total_hits' => 0];

        return [
            'categories' => $categories,
            'versions'   => $versions,
            'code_langs' => $codeLangs,
            'contexts'   => $contexts,
            'stats'      => $stats,
        ];
    }
}
