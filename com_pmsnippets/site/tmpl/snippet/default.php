<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;
use PontoMega\Component\Pmsnippets\Site\Helper\RouteHelper;

/** @var \PontoMega\Component\Pmsnippets\Site\View\Snippet\HtmlView $this */
$item       = $this->item;
$compParams = ComponentHelper::getParams('com_pmsnippets');
$codeLangs  = PmsnippetsHelper::getCodeLanguageOptions();
$contexts   = PmsnippetsHelper::getContextOptions();
$diffs      = PmsnippetsHelper::getDifficultyOptions();

$backUrl   = Route::_(RouteHelper::getSnippetsRoute(0, (string) $item->language));
$catUrl    = Route::_(RouteHelper::getSnippetsRoute((int) $item->catid, (string) $item->language));
$copyUrl   = Route::_('index.php?option=com_pmsnippets&task=snippet.copy', false);
$usefulUrl = Route::_('index.php?option=com_pmsnippets&task=snippet.useful', false);
$token     = Session::getFormToken();
?>
<article class="com-pmsnippets pms-detail"
         data-pms-detail
         data-snippet-id="<?php echo (int) $item->id; ?>"
         data-copy-url="<?php echo htmlspecialchars($copyUrl, ENT_QUOTES, 'UTF-8'); ?>"
         data-useful-url="<?php echo htmlspecialchars($usefulUrl, ENT_QUOTES, 'UTF-8'); ?>"
         data-csrf-token="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="pms-detail__top-nav">
        <a href="<?php echo htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="pms-back-link">
            <span aria-hidden="true">&larr;</span>
            <?php echo Text::_('COM_PMSNIPPETS_BACK_TO_LIBRARY'); ?>
        </a>
        <?php if (!empty($item->category_title)) : ?>
            <a href="<?php echo htmlspecialchars($catUrl, ENT_QUOTES, 'UTF-8'); ?>" class="pms-badge pms-badge--brand">
                <?php echo htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endif; ?>
    </div>

    <header class="pms-detail__header">
        <div class="pms-detail__badges">
            <span class="pms-badge pms-badge--brand">
                <?php echo htmlspecialchars($codeLangs[$item->code_language] ?? strtoupper((string) $item->code_language), ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <?php foreach ($item->versions as $ver) : ?>
                <span class="pms-badge pms-badge--dark">
                    <?php echo htmlspecialchars(PmsnippetsHelper::getVersionOptions()[$ver] ?? strtoupper($ver), ENT_QUOTES, 'UTF-8'); ?>
                </span>
            <?php endforeach; ?>
            <span class="pms-badge pms-badge--muted">
                <?php echo htmlspecialchars($contexts[$item->context_type] ?? ucfirst((string) $item->context_type), ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <span class="pms-badge pms-badge--muted">
                <?php echo htmlspecialchars($diffs[$item->difficulty] ?? ucfirst((string) $item->difficulty), ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>

        <h1 class="pms-detail__title"><?php echo $this->escape($item->title); ?></h1>

        <?php if (!empty($item->summary)) : ?>
            <p class="pms-detail__summary"><?php echo $this->escape($item->summary); ?></p>
        <?php endif; ?>

        <div class="pms-detail__meta">
            <span>
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <?php echo Text::sprintf('COM_PMSNIPPETS_UPDATED_ON', HTMLHelper::_('date', $item->modified ?: $item->created, Text::_('DATE_FORMAT_LC3'))); ?>
            </span>
            <span>
                <i class="fa-regular fa-copy" aria-hidden="true"></i>
                <span data-pms-copy-count="<?php echo (int) $item->id; ?>"><?php echo (int) $item->copies_count; ?></span>
                <?php echo Text::_('COM_PMSNIPPETS_COPIES_LABEL'); ?>
            </span>
            <span>
                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                <?php echo (int) $item->hits; ?>
                <?php echo Text::_('COM_PMSNIPPETS_HITS_LABEL'); ?>
            </span>
        </div>
    </header>

    <?php if (!empty($item->blocks)) : ?>
        <section class="pms-code-workbench" data-pms-workbench>
            <div class="pms-workbench__bar">
                <div class="pms-workbench__tabs" role="tablist">
                    <?php foreach ($item->blocks as $idx => $block) : ?>
                        <button type="button"
                                class="pms-workbench__tab<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                                role="tab"
                                aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                                data-pms-tab="<?php echo $idx; ?>">
                            <?php echo htmlspecialchars($block['label'] ?: strtoupper($block['version_tag']), ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="pms-workbench__actions">
                    <button type="button"
                            class="pms-wb-btn"
                            data-pms-download-btn
                            data-filename="<?php echo htmlspecialchars($item->alias, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fa-solid fa-download" aria-hidden="true"></i>
                        <span><?php echo Text::_('COM_PMSNIPPETS_DOWNLOAD_FILE'); ?></span>
                    </button>
                    <button type="button"
                            class="pms-wb-btn pms-wb-btn--primary"
                            data-pms-copy-active
                            data-snippet-id="<?php echo (int) $item->id; ?>">
                        <i class="fa-regular fa-copy" aria-hidden="true"></i>
                        <span data-pms-copy-label><?php echo Text::_('COM_PMSNIPPETS_COPY_CODE'); ?></span>
                    </button>
                </div>
            </div>

            <?php foreach ($item->blocks as $idx => $block) :
                $ext = PmsnippetsHelper::getFileExtension($block['code_language'] ?: $item->code_language);
                ?>
                <div class="pms-workbench__panel<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                     role="tabpanel"
                     data-pms-panel="<?php echo $idx; ?>"
                     data-file-ext="<?php echo htmlspecialchars($ext, ENT_QUOTES, 'UTF-8'); ?>">

                    <?php if (!empty($block['filename_hint'])) : ?>
                        <div class="pms-workbench__filehint">
                            <i class="fa-regular fa-file-code" aria-hidden="true"></i>
                            <span><?php echo htmlspecialchars($block['filename_hint'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>

                    <pre class="pms-code-pre pms-code-pre--full"><code class="pms-code" data-pms-lang="<?php echo htmlspecialchars($block['code_language'] ?: $item->code_language, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($block['code'], ENT_QUOTES, 'UTF-8'); ?></code></pre>
                    <textarea class="visually-hidden" aria-hidden="true" data-pms-raw-code><?php echo htmlspecialchars($block['code'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                    <?php if (!empty($block['notes'])) : ?>
                        <div class="pms-workbench__note">
                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                            <span><?php echo htmlspecialchars($block['notes'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <?php if (!empty($item->description)) : ?>
        <section class="pms-detail__explanation">
            <h2><?php echo Text::_('COM_PMSNIPPETS_EXPLANATION_HEADING'); ?></h2>
            <div class="pms-detail__prose">
                <?php echo $item->description; ?>
            </div>
        </section>
    <?php endif; ?>

    <div class="pms-detail__utility-bar">
        <?php if ((int) $compParams->get('enable_useful_vote', 1) === 1) : ?>
            <div class="pms-useful-box">
                <button type="button"
                        class="pms-btn pms-btn--outline"
                        data-pms-useful-btn
                        data-snippet-id="<?php echo (int) $item->id; ?>">
                    <i class="fa-regular fa-thumbs-up" aria-hidden="true"></i>
                    <span data-pms-useful-label><?php echo Text::_('COM_PMSNIPPETS_USEFUL_BUTTON'); ?></span>
                    <span class="pms-useful-count" data-pms-useful-count><?php echo (int) $item->useful_count; ?></span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (!empty($item->source_url)) : ?>
            <a href="<?php echo htmlspecialchars($item->source_url, ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="pms-source-link">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                <?php echo Text::_('COM_PMSNIPPETS_OFFICIAL_REFERENCE'); ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($item->tags->itemTags)) : ?>
        <div class="pms-detail__tags">
            <?php $tagLayout = new FileLayout('joomla.content.tags'); ?>
            <?php echo $tagLayout->render($item->tags->itemTags); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->related)) : ?>
        <section class="pms-related">
            <h2 class="pms-related__heading"><?php echo Text::_('COM_PMSNIPPETS_RELATED_TITLE'); ?></h2>
            <div class="pms-related__grid">
                <?php foreach ($this->related as $rel) :
                    $relUrl = Route::_(RouteHelper::getSnippetRoute($rel->slug, (int) $rel->catid, (string) $rel->language));
                    ?>
                    <article class="pms-related-card">
                        <div class="pms-card__badges mb-2">
                            <span class="pms-badge pms-badge--brand"><?php echo htmlspecialchars(strtoupper((string) $rel->code_language), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php foreach ($rel->versions as $rv) : ?>
                                <span class="pms-badge pms-badge--dark"><?php echo htmlspecialchars(PmsnippetsHelper::getVersionShortLabel($rv), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <h3>
                            <a href="<?php echo htmlspecialchars($relUrl, ENT_QUOTES, 'UTF-8'); ?>" class="stretched-link">
                                <?php echo htmlspecialchars($rel->title, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </h3>
                        <p><?php echo htmlspecialchars($rel->summary, ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</article>
