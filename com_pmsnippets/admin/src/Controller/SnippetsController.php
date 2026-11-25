<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

class SnippetsController extends AdminController
{
    public function __construct($config = [], $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);
        $this->registerTask('unfeatured', 'featured');
    }

    public function getModel($name = 'Snippet', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function featured(): void
    {
        $this->checkToken();

        $ids   = (array) $this->input->get('cid', [], 'int');
        $ids   = ArrayHelper::toInteger($ids);
        $value = $this->getTask() === 'featured' ? 1 : 0;

        if (!empty($ids)) {
            $model = $this->getModel();
            if ($model->featured($ids, $value)) {
                $msgKey = $value ? 'COM_PMSNIPPETS_N_ITEMS_FEATURED' : 'COM_PMSNIPPETS_N_ITEMS_UNFEATURED';
                $this->setMessage(Text::plural($msgKey, \count($ids)));
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_pmsnippets&view=snippets', false));
    }
}
