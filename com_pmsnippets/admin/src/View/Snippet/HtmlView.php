<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Administrator\View\Snippet;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;

    public function display($tpl = null): void
    {
        $model       = $this->getModel();
        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();

        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew   = ((int) $this->item->id === 0);
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_PMSNIPPETS_SNIPPET_NEW' : 'COM_PMSNIPPETS_SNIPPET_EDIT'),
            'code'
        );

        $toolbar->apply('snippet.apply');

        $saveGroup = $toolbar->dropdownButton('save-group');
        $saveGroup->configure(
            static function (Toolbar $childBar) use ($isNew): void {
                $childBar->save('snippet.save');
                $childBar->save2new('snippet.save2new');
                if (!$isNew) {
                    $childBar->save2copy('snippet.save2copy');
                }
            }
        );

        $toolbar->cancel('snippet.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
