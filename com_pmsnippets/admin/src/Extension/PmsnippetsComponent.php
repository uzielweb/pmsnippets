<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Administrator\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Association\AssociationServiceTrait;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Tag\TagServiceInterface;
use Joomla\CMS\Tag\TagServiceTrait;
use Psr\Container\ContainerInterface;

class PmsnippetsComponent extends MVCComponent implements
    BootableExtensionInterface,
    CategoryServiceInterface,
    RouterServiceInterface,
    TagServiceInterface,
    AssociationServiceInterface
{
    use CategoryServiceTrait;
    use RouterServiceTrait;
    use HTMLRegistryAwareTrait;
    use TagServiceTrait;
    use AssociationServiceTrait;

    public function boot(ContainerInterface $container): void
    {
    }

    protected function getTableNameForSection(?string $section = null): string
    {
        return 'pmsnippets_items';
    }

    protected function getStateColumnForSection(?string $section = null): string
    {
        return 'state';
    }
}
