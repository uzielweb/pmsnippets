<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;
use PontoMega\Component\Pmsnippets\Site\Helper\RouteHelper;

/** @var \PontoMega\Component\Pmsnippets\Site\View\Snippets\HtmlView|\PontoMega\Component\Pmsnippets\Site\View\Snippets\JsonView $this */
$codeLangs = PmsnippetsHelper::getCodeLanguageOptions();
$contexts  = PmsnippetsHelper::getContextOptions();

if (empty($this->items)) : ?>
    <div class="pms-empty-state">
        <i class="fa-solid fa-code pms-empty-state__icon" aria-hidden="true"></i>
        <h2><?php echo Text::_('COM_PMSNIPPETS_EMPTY_TITLE'); ?></h2>
        <p><?php echo Text::_('COM_PMSNIPPETS_EMPTY_DESC'); ?></p>
    </div>
<?php return;
endif; ?>

<div class="pms-grid">
    <?php foreach ($this->items as $item) :
        $detailUrl    = Route::_(RouteHelper::getSnippetRoute($item->slug, (int) $item->catid, (string) $item->language));
        $primaryBlock = $item->blocks[0] ?? null;
        $rawCode      = $primaryBlock ? (string) $primaryBlock['code'] : '';
        $codeLines    = explode("\n", $rawCode);
        $previewCode  = implode("\n", array_slice($codeLines, 0, 7));
        if (count($codeLines) > 7) {
            $previewCode .= "\n// ...";
        }
        $langLabel = $codeLangs[$item->code_language] ?? strtoupper((string) $item->code_language);
        $ctxLabel  = $contexts[$item->context_type] ?? ucfirst((string) $item->context_type);
        ?>
        <article class="pms-card" data-snippet-id="<?php echo (int) $item->id; ?>">
            <div class="pms-card__head">
                <div class="pms-card__badges">
                    <span class="pms-badge pms-badge--brand"><?php echo htmlspecialchars($langLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php foreach ($item->versions as $ver) : ?>
                        <span class="pms-badge pms-badge--dark"><?php echo htmlspecialchars(PmsnippetsHelper::getVersionShortLabel($ver), ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endforeach; ?>
                    <span class="pms-badge pms-badge--muted"><?php echo htmlspecialchars($ctxLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php if (!empty($item->category_title)) : ?>
                    <span class="pms-card__cat"><?php echo htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>

            <div class="pms-card__body">
                <h2 class="pms-card__title">
                    <a href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </h2>
                <?php if (!empty($item->summary)) : ?>
                    <p class="pms-card__summary"><?php echo htmlspecialchars($item->summary, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>

            <?php if ($previewCode !== '') : ?>
                <div class="pms-card__code-wrap">
                    <pre class="pms-code-pre"><code class="pms-code" data-pms-lang="<?php echo htmlspecialchars($primaryBlock['code_language'] ?? $item->code_language, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($previewCode, ENT_QUOTES, 'UTF-8'); ?></code></pre>
                    <textarea class="visually-hidden" aria-hidden="true" data-pms-raw-code><?php echo htmlspecialchars($rawCode, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            <?php endif; ?>

            <div class="pms-card__foot">
                <div class="pms-card__counters">
                    <span title="<?php echo htmlspecialchars(Text::_('COM_PMSNIPPETS_COPIES_LABEL'), ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fa-regular fa-copy" aria-hidden="true"></i>
                        <span data-pms-copy-count="<?php echo (int) $item->id; ?>"><?php echo (int) $item->copies_count; ?></span>
                    </span>
                    <span title="<?php echo htmlspecialchars(Text::_('COM_PMSNIPPETS_HITS_LABEL'), ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        <?php echo (int) $item->hits; ?>
                    </span>
                    <?php if (count($item->blocks) > 1) : ?>
                        <span class="pms-card__variants">
                            <?php echo Text::sprintf('COM_PMSNIPPETS_VARIANTS_COUNT', count($item->blocks)); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="pms-card__actions">
                    <?php if ($rawCode !== '') : ?>
                        <button type="button"
                                class="pms-btn-inline"
                                data-pms-copy-btn
                                data-snippet-id="<?php echo (int) $item->id; ?>">
                            <i class="fa-regular fa-copy" aria-hidden="true"></i>
                            <span data-pms-copy-label><?php echo Text::_('COM_PMSNIPPETS_COPY_CODE'); ?></span>
                        </button>
                    <?php endif; ?>
                    <a href="<?php echo htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>" class="pms-card__open">
                        <?php echo Text::_('COM_PMSNIPPETS_OPEN_SNIPPET'); ?>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
