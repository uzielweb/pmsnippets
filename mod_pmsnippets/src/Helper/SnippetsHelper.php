<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Module\Pmsnippets\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;

class SnippetsHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    public function getItems(Registry $params, SiteApplication $app): array
    {
        $db     = $this->getDatabase();
        $user   = $app->getIdentity();
        $groups = $user ? $user->getAuthorisedViewLevels() : [1];
        $lang   = Multilanguage::isEnabled() ? $app->getLanguage()->getTag() : '';
        $count  = max(1, min(12, (int) $params->get('count', 4)));
        $catid  = (int) $params->get('catid', 0);
        $mode   = (string) $params->get('mode', 'featured');

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('a.id'),
                $db->quoteName('a.catid'),
                $db->quoteName('a.title'),
                $db->quoteName('a.alias'),
                $db->quoteName('a.summary'),
                $db->quoteName('a.code_language'),
                $db->quoteName('a.context_type'),
                $db->quoteName('a.code_blocks'),
                $db->quoteName('a.copies_count'),
                $db->quoteName('a.hits'),
                $db->quoteName('a.language'),
                $db->quoteName('c.title', 'category_title'),
                '(SELECT GROUP_CONCAT(v.version_code ORDER BY v.version_code DESC SEPARATOR \',\') FROM ' . $db->quoteName('#__pmsnippets_item_versions', 'v') . ' WHERE v.snippet_id = a.id) AS versions_csv',
            ])
            ->from($db->quoteName('#__pmsnippets_items', 'a'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->whereIn($db->quoteName('a.access'), $groups);

        if ($lang !== '') {
            $query->whereIn($db->quoteName('a.language'), [$lang, '*'], ParameterType::STRING);
        }

        if ($catid > 0) {
            $query->where($db->quoteName('a.catid') . ' = :catid')
                ->bind(':catid', $catid, ParameterType::INTEGER);
        }

        switch ($mode) {
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
            default:
                $query->order($db->quoteName('a.featured') . ' DESC')
                    ->order($db->quoteName('a.ordering') . ' ASC')
                    ->order($db->quoteName('a.copies_count') . ' DESC');
                break;
        }

        $items = $db->setQuery($query, 0, $count)->loadObjectList() ?: [];

        foreach ($items as $item) {
            $item->blocks   = PmsnippetsHelper::parseCodeBlocks($item->code_blocks ?? '');
            $item->versions = array_values(array_filter(explode(',', (string) ($item->versions_csv ?? ''))));
            $item->slug     = $item->id . ':' . $item->alias;
        }

        return $items;
    }

    public function getCategories(SiteApplication $app): array
    {
        $db     = $this->getDatabase();
        $user   = $app->getIdentity();
        $groups = $user ? $user->getAuthorisedViewLevels() : [1];
        $lang   = Multilanguage::isEnabled() ? $app->getLanguage()->getTag() : '';

        $query = $db->getQuery(true)
            ->select([$db->quoteName('c.id'), $db->quoteName('c.title'), 'COUNT(' . $db->quoteName('a.id') . ') AS qty'])
            ->from($db->quoteName('#__categories', 'c'))
            ->join('INNER', $db->quoteName('#__pmsnippets_items', 'a'), $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id'))
            ->where($db->quoteName('c.extension') . ' = ' . $db->quote('com_pmsnippets'))
            ->where($db->quoteName('c.published') . ' = 1')
            ->where($db->quoteName('a.state') . ' = 1')
            ->whereIn($db->quoteName('a.access'), $groups)
            ->group([$db->quoteName('c.id'), $db->quoteName('c.title')])
            ->order($db->quoteName('c.lft') . ' ASC');

        if ($lang !== '') {
            $query->whereIn($db->quoteName('a.language'), [$lang, '*'], ParameterType::STRING);
        }

        return $db->setQuery($query, 0, 8)->loadObjectList() ?: [];
    }

    public function getStats(SiteApplication $app): object
    {
        $db     = $this->getDatabase();
        $user   = $app->getIdentity();
        $groups = $user ? $user->getAuthorisedViewLevels() : [1];
        $lang   = Multilanguage::isEnabled() ? $app->getLanguage()->getTag() : '';

        $query = $db->getQuery(true)
            ->select([
                'COUNT(' . $db->quoteName('a.id') . ') AS total_items',
                'COALESCE(SUM(' . $db->quoteName('a.copies_count') . '), 0) AS total_copies',
            ])
            ->from($db->quoteName('#__pmsnippets_items', 'a'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->whereIn($db->quoteName('a.access'), $groups);

        if ($lang !== '') {
            $query->whereIn($db->quoteName('a.language'), [$lang, '*'], ParameterType::STRING);
        }

        return $db->setQuery($query)->loadObject() ?: (object) ['total_items' => 0, 'total_copies' => 0];
    }
}
