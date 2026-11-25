<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Module\Pmsnippets\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        /** @var \PontoMega\Module\Pmsnippets\Site\Helper\SnippetsHelper $helper */
        $helper = $this->getHelperFactory()->getHelper('SnippetsHelper');

        $app                = $this->getApplication();
        $data['list']       = $helper->getItems($data['params'], $app);
        $data['categories'] = $helper->getCategories($app);
        $data['stats']      = $helper->getStats($app);

        return $data;
    }
}
