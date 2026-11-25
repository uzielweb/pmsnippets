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
use Joomla\CMS\Session\Session;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;
use PontoMega\Component\Pmsnippets\Site\Helper\RouteHelper;

if (empty($list)) {
    return;
}

$app->getLanguage()->load('com_pmsnippets', JPATH_SITE);

$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_pmsnippets');
$wa->useStyle('com_pmsnippets.site')
    ->useScript('com_pmsnippets.highlighter')
    ->useScript('com_pmsnippets.app');

Text::script('COM_PMSNIPPETS_COPIED');
Text::script('COM_PMSNIPPETS_COPY_CODE');

$langTag    = $app->getLanguage()->getTag();
$libraryUrl = Route::_(RouteHelper::getSnippetsRoute(0, $langTag));
$copyUrl    = Route::_('index.php?option=com_pmsnippets&task=snippet.copy', false);
$token      = Session::getFormToken();
?>
<div class="mod-pmsnippets pms-home-showcase"
     data-pms-home-module
     data-copy-url="<?php echo htmlspecialchars($copyUrl, ENT_QUOTES, 'UTF-8'); ?>"
     data-csrf-token="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="pms-home-showcase__intro">
        <span class="pms-eyebrow"><?php echo Text::_('MOD_PMSNIPPETS_HOME_EYEBROW'); ?></span>
        <h2 class="pms-home-showcase__title"><?php echo Text::_('MOD_PMSNIPPETS_HOME_TITLE'); ?></h2>
        <p class="pms-home-showcase__lead"><?php echo Text::_('MOD_PMSNIPPETS_HOME_SUBTITLE'); ?></p>

        <?php if ((int) $params->get('show_search', 1) === 1) : ?>
            <form action="<?php echo htmlspecialchars($libraryUrl, ENT_QUOTES, 'UTF-8'); ?>" method="get" class="pms-home-search">
                <label for="pms-home-q-<?php echo (int) $module->id; ?>" class="visually-hidden">
                    <?php echo Text::_('MOD_PMSNIPPETS_HOME_SEARCH_PLACEHOLDER'); ?>
                </label>
                <input type="search"
                       id="pms-home-q-<?php echo (int) $module->id; ?>"
                       name="q"
                       placeholder="<?php echo htmlspecialchars(Text::_('MOD_PMSNIPPETS_HOME_SEARCH_PLACEHOLDER'), ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="pms-btn pms-btn--primary">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <?php echo Text::_('MOD_PMSNIPPETS_HOME_SEARCH_BTN'); ?>
                </button>
            </form>
        <?php endif; ?>

        <?php if ((int) $params->get('show_stats', 1) === 1) : ?>
            <div class="pms-home-metrics">
                <div class="pms-stat">
                    <span class="pms-stat__value"><?php echo (int) $stats->total_items; ?></span>
                    <span class="pms-stat__label"><?php echo Text::_('MOD_PMSNIPPETS_STAT_SNIPPETS'); ?></span>
                </div>
                <div class="pms-stat">
                    <span class="pms-stat__value"><?php echo count($categories); ?></span>
                    <span class="pms-stat__label"><?php echo Text::_('MOD_PMSNIPPETS_STAT_CATEGORIES'); ?></span>
                </div>
                <div class="pms-stat">
                    <span class="pms-stat__value"><?php echo Text::_('MOD_PMSNIPPETS_STAT_VERSIONS_VALUE'); ?></span>
                    <span class="pms-stat__label"><?php echo Text::_('MOD_PMSNIPPETS_STAT_VERSIONS'); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ((int) $params->get('show_categories', 1) === 1 && !empty($categories)) : ?>
            <div class="pms-home-cats">
                <?php foreach ($categories as $cat) :
                    $catHref = Route::_(RouteHelper::getSnippetsRoute((int) $cat->id, $langTag));
                    ?>
                    <a href="<?php echo htmlspecialchars($catHref, ENT_QUOTES, 'UTF-8'); ?>" class="pms-home-cat-link">
                        <?php echo htmlspecialchars($cat->title . ' (' . (int) $cat->qty . ')', ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="<?php echo htmlspecialchars($libraryUrl, ENT_QUOTES, 'UTF-8'); ?>" class="pms-btn pms-btn--primary">
            <?php echo Text::_('MOD_PMSNIPPETS_EXPLORE_ALL'); ?>
            <span aria-hidden="true">&rarr;</span>
        </a>
    </div>

    <div class="pms-home-console">
        <div class="pms-home-console__tabs" role="tablist">
            <?php foreach ($list as $idx => $item) : ?>
                <button type="button"
                        class="pms-home-console__tab<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                        role="tab"
                        aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                        data-pms-home-tab="<?php echo $idx; ?>">
                    <span class="pms-home-console__tab-cat">
                        <?php echo htmlspecialchars($item->category_title ?: strtoupper((string) $item->code_language), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="pms-home-console__tab-title">
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($list as $idx => $item) :
            $primaryBlock = $item->blocks[0] ?? null;
            $rawCode      = $primaryBlock ? (string) $primaryBlock['code'] : '';
            $codeLines    = explode("\n", $rawCode);
            $previewCode  = implode("\n", array_slice($codeLines, 0, 14));
            if (count($codeLines) > 14) {
                $previewCode .= "\n// ...";
            }
            $itemUrl = Route::_(RouteHelper::getSnippetRoute($item->slug, (int) $item->catid, (string) $item->language));
            ?>
            <div class="pms-home-console__panel<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                 role="tabpanel"
                 data-pms-home-panel="<?php echo $idx; ?>">
                <div class="pms-home-console__meta">
                    <h3><?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="pms-card__badges">
                        <?php foreach ($item->versions as $ver) : ?>
                            <span class="pms-badge pms-badge--brand"><?php echo htmlspecialchars(PmsnippetsHelper::getVersionShortLabel($ver), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($previewCode !== '') : ?>
                    <pre class="pms-code-pre pms-code-pre--full"><code class="pms-code" data-pms-lang="<?php echo htmlspecialchars($primaryBlock['code_language'] ?? $item->code_language, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($previewCode, ENT_QUOTES, 'UTF-8'); ?></code></pre>
                    <textarea class="visually-hidden" aria-hidden="true" data-pms-raw-code><?php echo htmlspecialchars($rawCode, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php endif; ?>

                <div class="pms-home-console__foot">
                    <a href="<?php echo htmlspecialchars($itemUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo Text::_('MOD_PMSNIPPETS_OPEN_FULL'); ?>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                    <?php if ($rawCode !== '') : ?>
                        <button type="button"
                                class="pms-wb-btn pms-wb-btn--primary"
                                data-pms-copy-btn
                                data-snippet-id="<?php echo (int) $item->id; ?>">
                            <i class="fa-regular fa-copy" aria-hidden="true"></i>
                            <span data-pms-copy-label><?php echo Text::_('COM_PMSNIPPETS_COPY_CODE'); ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
