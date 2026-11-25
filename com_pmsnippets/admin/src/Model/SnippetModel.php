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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\ParameterType;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;

class SnippetModel extends AdminModel
{
    public $typeAlias = 'com_pmsnippets.snippet';

    protected $associationsContext = 'com_pmsnippets.item';

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_pmsnippets.snippet', 'snippet', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_pmsnippets.edit.snippet.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_pmsnippets.snippet', $data);

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item) {
            $blocks = PmsnippetsHelper::parseCodeBlocks($item->code_blocks ?? '');
            $mapped = [];
            foreach ($blocks as $idx => $block) {
                $mapped['code_blocks' . $idx] = $block;
            }
            $item->code_blocks = $mapped;

            if (!empty($item->id)) {
                $item->tags = new TagsHelper();
                $item->tags->getTagIds($item->id, 'com_pmsnippets.snippet');

                if (Associations::isEnabled()) {
                    $associations       = Associations::getAssociations('com_pmsnippets', '#__pmsnippets_items', 'com_pmsnippets.item', $item->id, 'id', 'alias', 'catid');
                    $item->associations = [];
                    foreach ($associations as $tag => $association) {
                        $item->associations[$tag] = $association->id;
                    }
                }
            }
        }

        return $item;
    }

    protected function preprocessForm(Form $form, $data, $group = 'content'): void
    {
        if (Associations::isEnabled()) {
            $languages = LanguageHelper::getContentLanguages(false, false, null, 'ordering', 'asc');

            if (\count($languages) > 1) {
                $addform = new \SimpleXMLElement('<form />');
                $fields  = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language) {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'sql');
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('layout', 'joomla.form.field.list-fancy-select');
                    $field->addAttribute('query', 'SELECT id AS value, title AS text FROM #__pmsnippets_items WHERE language = ' . $this->getDatabase()->quote($language->lang_code) . ' AND state >= 0 ORDER BY title ASC');
                    $field->addAttribute('key_field', 'value');
                    $field->addAttribute('value_field', 'text');
                    $opt = $field->addChild('option', 'JGLOBAL_ASSOCIATIONS_NONE');
                    $opt->addAttribute('value', '');
                }

                $form->load($addform, false);
            }
        }

        parent::preprocessForm($form, $data, $group);
    }

    public function save($data): bool
    {
        PluginHelper::importPlugin('finder');
        PluginHelper::importPlugin('content');

        $saved = parent::save($data);

        if ($saved) {
            $id     = (int) $this->getState($this->getName() . '.id');
            $blocks = PmsnippetsHelper::parseCodeBlocks($data['code_blocks'] ?? '');
            $this->syncVersions($id, PmsnippetsHelper::extractVersions($blocks));
        }

        return $saved;
    }

    public function featured(array $pks, int $value = 0): bool
    {
        $pks = array_filter(array_map('intval', $pks));

        if (empty($pks)) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__pmsnippets_items'))
            ->set($db->quoteName('featured') . ' = :featured')
            ->whereIn($db->quoteName('id'), $pks)
            ->bind(':featured', $value, ParameterType::INTEGER);

        $db->setQuery($query)->execute();

        $this->cleanCache();

        return true;
    }

    private function syncVersions(int $snippetId, array $versions): void
    {
        if ($snippetId <= 0) {
            return;
        }

        $db = $this->getDatabase();

        $del = $db->getQuery(true)
            ->delete($db->quoteName('#__pmsnippets_item_versions'))
            ->where($db->quoteName('snippet_id') . ' = :sid')
            ->bind(':sid', $snippetId, ParameterType::INTEGER);
        $db->setQuery($del)->execute();

        foreach ($versions as $ver) {
            $ins = $db->getQuery(true)
                ->insert($db->quoteName('#__pmsnippets_item_versions'))
                ->columns([$db->quoteName('snippet_id'), $db->quoteName('version_code')])
                ->values(':sid, :ver')
                ->bind(':sid', $snippetId, ParameterType::INTEGER)
                ->bind(':ver', $ver, ParameterType::STRING);
            $db->setQuery($ins)->execute();
        }
    }
}
