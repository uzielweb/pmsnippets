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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\Response\JsonResponse;

class JsonView extends AbstractView
{
    public array $items = [];
    public $pagination;
    public $state;
    public $params;

    public function display($tpl = null): void
    {
        /** @var \PontoMega\Component\Pmsnippets\Site\Model\SnippetsModel $model */
        $model            = $this->getModel();
        $this->items      = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state      = $model->getState();
        $this->params     = $this->state->get('params');

        $partialPath = JPATH_SITE . '/components/com_pmsnippets/tmpl/snippets/default_items.php';

        ob_start();
        require $partialPath;
        $html = ob_get_clean();

        $total = (int) $this->pagination->total;
        $shown = \count($this->items);

        echo new JsonResponse([
            'html'       => $html,
            'total'      => $total,
            'shown'      => $shown,
            'summary'    => Text::sprintf('COM_PMSNIPPETS_RESULTS_SUMMARY', $shown, $total),
            'pagination' => $this->pagination->pagesTotal > 1 ? $this->pagination->getPagesLinks() : '',
        ]);
    }
}
