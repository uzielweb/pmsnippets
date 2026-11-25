<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Association\AssociationExtensionInterface;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use PontoMega\Component\Pmsnippets\Administrator\Extension\PmsnippetsComponent;
use PontoMega\Component\Pmsnippets\Administrator\Helper\AssociationsHelper;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(AssociationExtensionInterface::class, new AssociationsHelper());

        $container->registerServiceProvider(new CategoryFactory('\\PontoMega\\Component\\Pmsnippets'));
        $container->registerServiceProvider(new MVCFactory('\\PontoMega\\Component\\Pmsnippets'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\PontoMega\\Component\\Pmsnippets'));
        $container->registerServiceProvider(new RouterFactory('\\PontoMega\\Component\\Pmsnippets'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new PmsnippetsComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
                $component->setAssociationExtension($container->get(AssociationExtensionInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};
