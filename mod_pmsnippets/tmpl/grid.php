<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;
use PontoMega\Component\Pmsnippets\Site\Helper\RouteHelper;

if (empty($list)) {
    return;
}

$app->getLanguage()->load('com_pmsnippets', JPATH_SITE);
$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_pmsnippets');
$wa->useStyle('com_pmsnippets.site');
?>
<div class="mod-pmsnippets pms-related__grid">
    <?php foreach ($list as $item) :
        $url = Route::_(RouteHelper::getSnippetRoute($item->slug, (int) $item->catid, (string) $item->language));
        ?>
        <article class="pms-related-card">
            <div class="pms-card__badges mb-2">
                <span class="pms-badge pms-badge--brand"><?php echo htmlspecialchars(strtoupper((string) $item->code_language), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php foreach ($item->versions as $ver) : ?>
                    <span class="pms-badge pms-badge--dark"><?php echo htmlspecialchars(PmsnippetsHelper::getVersionShortLabel($ver), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endforeach; ?>
            </div>
            <h3>
                <a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" class="stretched-link">
                    <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </h3>
            <p><?php echo htmlspecialchars($item->summary, ENT_QUOTES, 'UTF-8'); ?></p>
        </article>
    <?php endforeach; ?>
</div>
