<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    protected $default_view = 'snippets';

    public function display($cachable = false, $urlparams = [])
    {
        $safeUrlParams = [
            'id'         => 'INT',
            'catid'      => 'INT',
            'q'          => 'STRING',
            'version'    => 'CMD',
            'code_lang'  => 'CMD',
            'context'    => 'CMD',
            'difficulty' => 'CMD',
            'tag'        => 'INT',
            'sort'       => 'CMD',
            'limit'      => 'UINT',
            'limitstart' => 'UINT',
            'lang'       => 'CMD',
        ];

        $viewName = $this->input->getCmd('view', $this->default_view);
        $isAjax   = ($this->input->getCmd('format', 'html') === 'json');

        return parent::display(!$isAjax && $viewName === 'snippet', $safeUrlParams);
    }
}
