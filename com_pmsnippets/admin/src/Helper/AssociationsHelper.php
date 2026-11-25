<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Association\AssociationExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Table\Table;
use PontoMega\Component\Pmsnippets\Site\Helper\RouteHelper;

class AssociationsHelper extends AssociationExtensionHelper
{
    protected $extension = 'com_pmsnippets';

    protected $itemTypes = ['snippet', 'category'];

    protected $associationsSupport = true;

    public function getAssociationsForItem($id = 0, $view = null): array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $view = $view ?? $input->getCmd('view');
        $id   = empty($id) ? $input->getInt('id') : (int) $id;

        if ($view === 'snippet' && $id > 0) {
            $associations = Associations::getAssociations(
                $this->extension,
                '#__pmsnippets_items',
                'com_pmsnippets.item',
                $id,
                'id',
                'alias',
                'catid'
            );

            $return = [];
            foreach ($associations as $tag => $item) {
                $return[$tag] = RouteHelper::getSnippetRoute($item->id, (int) ($item->catid ?? 0), $tag);
            }

            return $return;
        }

        return [];
    }

    public function getAssociations($typeName = '', $id = 0): array
    {
        $typeName = (string) ($typeName ?? '');
        $id       = (int) $id;

        if ($typeName === '' || $id <= 0) {
            return [];
        }

        $type = $this->getType($typeName);

        if ($typeName === 'category') {
            $context    = 'com_categories.item';
            $catidField = '';
        } else {
            $context    = 'com_pmsnippets.item';
            $catidField = 'catid';
        }

        return Associations::getAssociations(
            $this->extension,
            $type['tables']['a'],
            $context,
            $id,
            'id',
            'alias',
            $catidField
        );
    }

    public function getItem($typeName, $id)
    {
        if (empty($id)) {
            return null;
        }

        if ($typeName === 'category') {
            $table = Table::getInstance('Category');
        } else {
            $table = new \PontoMega\Component\Pmsnippets\Administrator\Table\SnippetTable(
                \Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class)
            );
        }

        $table->load($id);

        return $table;
    }

    public function getType($typeName = ''): array
    {
        if ($typeName === 'category') {
            return [
                'fields'  => $this->getFieldsTemplate(),
                'support' => $this->getSupportTemplate(),
                'tables'  => ['a' => '#__categories'],
                'joins'   => [],
                'title'   => 'category',
            ];
        }

        return [
            'fields'  => $this->getFieldsTemplate(),
            'support' => $this->getSupportTemplate(),
            'tables'  => ['a' => '#__pmsnippets_items'],
            'joins'   => [],
            'title'   => 'snippet',
        ];
    }
}
