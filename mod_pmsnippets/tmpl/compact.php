<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use PontoMega\Component\Pmsnippets\Site\Helper\RouteHelper;

if (empty($list)) {
    return;
}

$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_pmsnippets');
$wa->useStyle('com_pmsnippets.site');
?>
<ul class="mod-pmsnippets list-unstyled m-0 p-0">
    <?php foreach ($list as $item) :
        $url = Route::_(RouteHelper::getSnippetRoute($item->slug, (int) $item->catid, (string) $item->language));
        ?>
        <li class="py-2 border-bottom">
            <a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" class="fw-semibold text-decoration-none">
                <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
