<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Site\View\Snippets;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $params;
    public array $facets = [];

    public function display($tpl = null): void
    {
        /** @var \PontoMega\Component\Pmsnippets\Site\Model\SnippetsModel $model */
        $model            = $this->getModel();
        $this->items      = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state      = $model->getState();
        $this->params     = $this->state->get('params');
        $this->facets     = $model->getFacets();

        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareDocument(): void
    {
        $app     = Factory::getApplication();
        $doc     = $this->getDocument();
        $wa      = $doc->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_pmsnippets');

        $wa->useStyle('com_pmsnippets.site')
            ->useScript('com_pmsnippets.highlighter')
            ->useScript('com_pmsnippets.app');

        Text::script('COM_PMSNIPPETS_COPIED');
        Text::script('COM_PMSNIPPETS_COPY_CODE');
        Text::script('COM_PMSNIPPETS_COPY_ERROR');
        Text::script('COM_PMSNIPPETS_LOADING');
        Text::script('COM_PMSNIPPETS_RESULTS_SUMMARY');

        $title = $this->params->get('page_title', '');
        if ($title === '') {
            $title = Text::_('COM_PMSNIPPETS_LIBRARY_TITLE');
        }
        $this->setDocumentTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $doc->setDescription($this->params->get('menu-meta_description'));
        }
    }
}
