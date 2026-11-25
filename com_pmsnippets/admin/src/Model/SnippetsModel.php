<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

class SnippetsModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'catid', 'a.catid', 'category_title',
                'code_language', 'a.code_language',
                'context_type', 'a.context_type',
                'difficulty', 'a.difficulty',
                'featured', 'a.featured',
                'state', 'a.state',
                'access', 'a.access', 'access_level',
                'ordering', 'a.ordering',
                'language', 'a.language',
                'hits', 'a.hits',
                'copies_count', 'a.copies_count',
                'useful_count', 'a.useful_count',
                'created', 'a.created',
                'version_code',
            ];
        }

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'ASC'): void
    {
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
        $this->setState('filter.category_id', $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '', 'string'));
        $this->setState('filter.version_code', $this->getUserStateFromRequest($this->context . '.filter.version_code', 'filter_version_code', '', 'string'));
        $this->setState('filter.code_language', $this->getUserStateFromRequest($this->context . '.filter.code_language', 'filter_code_language', '', 'string'));
        $this->setState('filter.context_type', $this->getUserStateFromRequest($this->context . '.filter.context_type', 'filter_context_type', '', 'string'));
        $this->setState('filter.language', $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '', 'string'));
        $this->setState('filter.access', $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'string'));

        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = ''): string
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.version_code');
        $id .= ':' . $this->getState('filter.code_language');
        $id .= ':' . $this->getState('filter.context_type');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . $this->getState('filter.access');

        return parent::getStoreId($id);
    }

    protected function getListQuery(): QueryInterface
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.alias'),
            $db->quoteName('a.catid'),
            $db->quoteName('a.code_language'),
            $db->quoteName('a.context_type'),
            $db->quoteName('a.difficulty'),
            $db->quoteName('a.featured'),
            $db->quoteName('a.state'),
            $db->quoteName('a.access'),
            $db->quoteName('a.ordering'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.copies_count'),
            $db->quoteName('a.useful_count'),
            $db->quoteName('a.language'),
            $db->quoteName('a.created'),
            $db->quoteName('a.checked_out'),
            $db->quoteName('a.checked_out_time'),
            $db->quoteName('c.title', 'category_title'),
            $db->quoteName('ag.title', 'access_level'),
            $db->quoteName('l.title', 'language_title'),
            $db->quoteName('l.image', 'language_image'),
            '(SELECT GROUP_CONCAT(v.version_code ORDER BY v.version_code DESC SEPARATOR \',\') FROM ' . $db->quoteName('#__pmsnippets_item_versions', 'v') . ' WHERE v.snippet_id = a.id) AS versions_csv',
        ])
            ->from($db->quoteName('#__pmsnippets_items', 'a'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'))
            ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

        if (Associations::isEnabled()) {
            $subQuery = $db->getQuery(true)
                ->select('COUNT(' . $db->quoteName('asso1.id') . ') > 1')
                ->from($db->quoteName('#__associations', 'asso1'))
                ->join('INNER', $db->quoteName('#__associations', 'asso2'), $db->quoteName('asso1.key') . ' = ' . $db->quoteName('asso2.key'))
                ->where([
                    $db->quoteName('asso1.id') . ' = ' . $db->quoteName('a.id'),
                    $db->quoteName('asso1.context') . ' = ' . $db->quote('com_pmsnippets.item'),
                ]);
            $query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
        }

        // State filter
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $state = (int) $state;
            $query->where($db->quoteName('a.state') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        } elseif ($state === '') {
            $query->where($db->quoteName('a.state') . ' IN (0, 1)');
        }

        // Category filter
        $catId = $this->getState('filter.category_id');
        if (is_numeric($catId) && (int) $catId > 0) {
            $catId = (int) $catId;
            $query->where($db->quoteName('a.catid') . ' = :catid')
                ->bind(':catid', $catId, ParameterType::INTEGER);
        }

        // Version filter via pivot table
        $versionCode = trim((string) $this->getState('filter.version_code'));
        if ($versionCode !== '') {
            $query->join('INNER', $db->quoteName('#__pmsnippets_item_versions', 'fv'), $db->quoteName('fv.snippet_id') . ' = ' . $db->quoteName('a.id'))
                ->where($db->quoteName('fv.version_code') . ' = :versionCode')
                ->bind(':versionCode', $versionCode, ParameterType::STRING);
        }

        // Code language filter
        $codeLang = trim((string) $this->getState('filter.code_language'));
        if ($codeLang !== '') {
            $query->where($db->quoteName('a.code_language') . ' = :codeLang')
                ->bind(':codeLang', $codeLang, ParameterType::STRING);
        }

        // Context filter
        $contextType = trim((string) $this->getState('filter.context_type'));
        if ($contextType !== '') {
            $query->where($db->quoteName('a.context_type') . ' = :contextType')
                ->bind(':contextType', $contextType, ParameterType::STRING);
        }

        // Site language filter
        $lang = trim((string) $this->getState('filter.language'));
        if ($lang !== '') {
            $query->where($db->quoteName('a.language') . ' = :lang')
                ->bind(':lang', $lang, ParameterType::STRING);
        }

        // Access filter
        $access = $this->getState('filter.access');
        if (is_numeric($access)) {
            $access = (int) $access;
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Search filter
        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            if (stripos($search, 'id:') === 0) {
                $id = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :searchId')
                    ->bind(':searchId', $id, ParameterType::INTEGER);
            } else {
                $like = '%' . str_replace(' ', '%', $search) . '%';
                $query->where('(' . $db->quoteName('a.title') . ' LIKE :s1 OR ' . $db->quoteName('a.summary') . ' LIKE :s2 OR ' . $db->quoteName('a.code_search_text') . ' LIKE :s3)')
                    ->bind(':s1', $like, ParameterType::STRING)
                    ->bind(':s2', $like, ParameterType::STRING)
                    ->bind(':s3', $like, ParameterType::STRING);
            }
        }

        $orderCol  = $this->state->get('list.ordering', 'a.ordering');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
