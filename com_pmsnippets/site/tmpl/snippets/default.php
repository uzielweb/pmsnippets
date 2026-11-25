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
use Joomla\CMS\Session\Session;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;

/** @var \PontoMega\Component\Pmsnippets\Site\View\Snippets\HtmlView $this */
$q          = (string) $this->state->get('filter.q', '');
$catid      = (int) $this->state->get('filter.catid', 0);
$version    = (string) $this->state->get('filter.version', '');
$codeLang   = (string) $this->state->get('filter.code_lang', '');
$context    = (string) $this->state->get('filter.context', '');
$difficulty = (string) $this->state->get('filter.difficulty', '');
$sort       = (string) $this->state->get('filter.sort', 'featured');

$stats       = $this->facets['stats'] ?? (object) ['total_items' => 0, 'total_copies' => 0, 'total_hits' => 0];
$categories  = $this->facets['categories'] ?? [];
$versions    = $this->facets['versions'] ?? [];
$codeLangs   = $this->facets['code_langs'] ?? [];
$contexts    = $this->facets['contexts'] ?? [];
$difficulties = PmsnippetsHelper::getDifficultyOptions();

$actionUrl = Route::_('index.php?option=com_pmsnippets&view=snippets');
$ajaxUrl   = Route::_('index.php?option=com_pmsnippets&view=snippets&format=json', false);
$copyUrl   = Route::_('index.php?option=com_pmsnippets&task=snippet.copy', false);
$token     = Session::getFormToken();

$hasActiveFilters = ($q !== '' || $catid > 0 || $version !== '' || $codeLang !== '' || $context !== '' || $difficulty !== '');
$heading          = $this->params->get('show_page_heading') && $this->params->get('page_heading')
    ? $this->params->get('page_heading')
    : Text::_('COM_PMSNIPPETS_LIBRARY_TITLE');
?>
<div class="com-pmsnippets pms-library"
     data-pms-library
     data-ajax-url="<?php echo htmlspecialchars($ajaxUrl, ENT_QUOTES, 'UTF-8'); ?>"
     data-copy-url="<?php echo htmlspecialchars($copyUrl, ENT_QUOTES, 'UTF-8'); ?>"
     data-csrf-token="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

    <header class="pms-library__header">
        <div class="pms-library__header-main">
            <span class="pms-eyebrow"><?php echo Text::_('COM_PMSNIPPETS_LIBRARY_EYEBROW'); ?></span>
            <h1 class="pms-library__title"><?php echo $this->escape($heading); ?></h1>
            <p class="pms-library__lead"><?php echo Text::_('COM_PMSNIPPETS_LIBRARY_SUBTITLE'); ?></p>
        </div>

        <div class="pms-library__stats">
            <div class="pms-stat">
                <span class="pms-stat__value"><?php echo (int) $stats->total_items; ?></span>
                <span class="pms-stat__label"><?php echo Text::_('COM_PMSNIPPETS_STAT_SNIPPETS'); ?></span>
            </div>
            <div class="pms-stat">
                <span class="pms-stat__value"><?php echo count($categories); ?></span>
                <span class="pms-stat__label"><?php echo Text::_('COM_PMSNIPPETS_STAT_CATEGORIES'); ?></span>
            </div>
            <div class="pms-stat">
                <span class="pms-stat__value"><?php echo Text::_('COM_PMSNIPPETS_STAT_VERSIONS_VALUE'); ?></span>
                <span class="pms-stat__label"><?php echo Text::_('COM_PMSNIPPETS_STAT_VERSIONS'); ?></span>
            </div>
        </div>
    </header>

    <form action="<?php echo htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8'); ?>"
          method="get"
          class="pms-filter-panel"
          data-pms-filter-form>

        <div class="pms-search-row">
            <div class="pms-search-field">
                <label for="pms-search-input" class="visually-hidden"><?php echo Text::_('COM_PMSNIPPETS_SEARCH_LABEL'); ?></label>
                <i class="fa-solid fa-magnifying-glass pms-search-field__icon" aria-hidden="true"></i>
                <input type="search"
                       id="pms-search-input"
                       name="q"
                       value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="<?php echo htmlspecialchars(Text::_('COM_PMSNIPPETS_SEARCH_PLACEHOLDER'), ENT_QUOTES, 'UTF-8'); ?>"
                       class="pms-search-field__input"
                       autocomplete="off"
                       data-pms-search>
            </div>
            <button type="submit" class="pms-btn pms-btn--primary">
                <?php echo Text::_('COM_PMSNIPPETS_SEARCH_SUBMIT'); ?>
            </button>
        </div>

        <div class="pms-filters-grid">
            <div class="pms-filter-group">
                <label for="pms-filter-catid" class="pms-filter-label"><?php echo Text::_('COM_PMSNIPPETS_FILTER_CATEGORY'); ?></label>
                <select id="pms-filter-catid" name="catid" class="pms-select" data-pms-filter>
                    <option value="0"><?php echo Text::_('COM_PMSNIPPETS_FILTER_ALL_CATEGORIES'); ?></option>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?php echo (int) $cat->id; ?>" <?php echo $catid === (int) $cat->id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat->title . ' (' . (int) $cat->qty . ')', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="pms-filter-group">
                <label for="pms-filter-version" class="pms-filter-label"><?php echo Text::_('COM_PMSNIPPETS_FILTER_VERSION'); ?></label>
                <select id="pms-filter-version" name="version" class="pms-select" data-pms-filter>
                    <option value=""><?php echo Text::_('COM_PMSNIPPETS_FILTER_ALL_VERSIONS'); ?></option>
                    <?php foreach ($versions as $ver) : ?>
                        <option value="<?php echo htmlspecialchars($ver->code, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $version === $ver->code ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ver->label . ' (' . (int) $ver->qty . ')', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="pms-filter-group">
                <label for="pms-filter-code-lang" class="pms-filter-label"><?php echo Text::_('COM_PMSNIPPETS_FILTER_CODE_LANG'); ?></label>
                <select id="pms-filter-code-lang" name="code_lang" class="pms-select" data-pms-filter>
                    <option value=""><?php echo Text::_('COM_PMSNIPPETS_FILTER_ALL_LANGS'); ?></option>
                    <?php foreach ($codeLangs as $cl) : ?>
                        <option value="<?php echo htmlspecialchars($cl->code, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $codeLang === $cl->code ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cl->label . ' (' . (int) $cl->qty . ')', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="pms-filter-group">
                <label for="pms-filter-context" class="pms-filter-label"><?php echo Text::_('COM_PMSNIPPETS_FILTER_CONTEXT'); ?></label>
                <select id="pms-filter-context" name="context" class="pms-select" data-pms-filter>
                    <option value=""><?php echo Text::_('COM_PMSNIPPETS_FILTER_ALL_CONTEXTS'); ?></option>
                    <?php foreach ($contexts as $ctx) : ?>
                        <option value="<?php echo htmlspecialchars($ctx->code, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $context === $ctx->code ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ctx->label . ' (' . (int) $ctx->qty . ')', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="pms-filter-group">
                <label for="pms-filter-difficulty" class="pms-filter-label"><?php echo Text::_('COM_PMSNIPPETS_FILTER_DIFFICULTY'); ?></label>
                <select id="pms-filter-difficulty" name="difficulty" class="pms-select" data-pms-filter>
                    <option value=""><?php echo Text::_('COM_PMSNIPPETS_FILTER_ALL_DIFFICULTIES'); ?></option>
                    <?php foreach ($difficulties as $dCode => $dLabel) : ?>
                        <option value="<?php echo htmlspecialchars($dCode, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $difficulty === $dCode ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="pms-filter-group">
                <label for="pms-filter-sort" class="pms-filter-label"><?php echo Text::_('COM_PMSNIPPETS_FILTER_SORT'); ?></label>
                <select id="pms-filter-sort" name="sort" class="pms-select" data-pms-filter>
                    <option value="featured" <?php echo $sort === 'featured' ? 'selected' : ''; ?>><?php echo Text::_('COM_PMSNIPPETS_SORT_FEATURED'); ?></option>
                    <option value="relevance" <?php echo $sort === 'relevance' ? 'selected' : ''; ?>><?php echo Text::_('COM_PMSNIPPETS_SORT_RELEVANCE'); ?></option>
                    <option value="copies" <?php echo $sort === 'copies' ? 'selected' : ''; ?>><?php echo Text::_('COM_PMSNIPPETS_SORT_COPIES'); ?></option>
                    <option value="hits" <?php echo $sort === 'hits' ? 'selected' : ''; ?>><?php echo Text::_('COM_PMSNIPPETS_SORT_HITS'); ?></option>
                    <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>><?php echo Text::_('COM_PMSNIPPETS_SORT_RECENT'); ?></option>
                    <option value="alpha" <?php echo $sort === 'alpha' ? 'selected' : ''; ?>><?php echo Text::_('COM_PMSNIPPETS_SORT_ALPHA'); ?></option>
                </select>
            </div>
        </div>

        <?php if (!empty($categories)) : ?>
            <div class="pms-quick-cats" role="group" aria-label="<?php echo Text::_('COM_PMSNIPPETS_FILTER_CATEGORY'); ?>">
                <button type="button"
                        class="pms-quick-cat<?php echo $catid === 0 ? ' is-active' : ''; ?>"
                        data-pms-quick-cat="0">
                    <?php echo Text::_('JALL'); ?>
                    <span><?php echo (int) $stats->total_items; ?></span>
                </button>
                <?php foreach ($categories as $cat) : ?>
                    <button type="button"
                            class="pms-quick-cat<?php echo $catid === (int) $cat->id ? ' is-active' : ''; ?>"
                            data-pms-quick-cat="<?php echo (int) $cat->id; ?>">
                        <?php echo htmlspecialchars($cat->title, ENT_QUOTES, 'UTF-8'); ?>
                        <span><?php echo (int) $cat->qty; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="pms-toolbar-meta">
            <div class="pms-results-summary" data-pms-summary aria-live="polite">
                <?php echo Text::sprintf('COM_PMSNIPPETS_RESULTS_SUMMARY', count($this->items), (int) $this->pagination->total); ?>
            </div>
            <button type="button"
                    class="pms-reset-btn<?php echo $hasActiveFilters ? '' : ' d-none'; ?>"
                    data-pms-reset>
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                <?php echo Text::_('COM_PMSNIPPETS_CLEAR_FILTERS'); ?>
            </button>
        </div>
    </form>

    <div class="pms-results-region" data-pms-results>
        <?php echo $this->loadTemplate('items'); ?>
    </div>

    <div class="pms-pagination-region" data-pms-pagination>
        <?php if ($this->pagination->pagesTotal > 1) : ?>
            <?php echo $this->pagination->getPagesLinks(); ?>
        <?php endif; ?>
    </div>
</div>
