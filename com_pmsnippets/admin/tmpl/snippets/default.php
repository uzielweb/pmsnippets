<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;

/** @var \PontoMega\Component\Pmsnippets\Administrator\View\Snippets\HtmlView $this */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder === 'a.ordering');

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_pmsnippets&task=snippets.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}

$codeLangs = PmsnippetsHelper::getCodeLanguageOptions();
$contexts  = PmsnippetsHelper::getContextOptions();
?>
<form action="<?php echo Route::_('index.php?option=com_pmsnippets&view=snippets'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table itemList" id="snippetList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_PMSNIPPETS_MANAGER_SNIPPETS'); ?>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                                </th>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JFEATURED', 'a.featured', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo Text::_('COM_PMSNIPPETS_COL_VERSIONS'); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-lg-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PMSNIPPETS_COL_CODE_LANG', 'a.code_language', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-lg-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_PMSNIPPETS_COL_COPIES', 'a.copies_count', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody <?php if ($saveOrder) : ?>class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>"<?php endif; ?>>
                            <?php foreach ($this->items as $i => $item) : ?>
                                <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo (int) $item->catid; ?>">
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <span class="sortable-handler<?php echo $saveOrder ? '' : ' inactive'; ?>">
                                            <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                        </span>
                                        <?php if ($saveOrder) : ?>
                                            <input type="text" name="order[]" size="5" value="<?php echo (int) $item->ordering; ?>" class="width-20 text-area-order hidden">
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'snippets.', true, 'cb'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php $featTask = $item->featured ? 'snippets.unfeatured' : 'snippets.featured'; ?>
                                        <a href="<?php echo Route::_('index.php?option=com_pmsnippets&task=' . $featTask . '&cid[]=' . (int) $item->id . '&' . Session::getFormToken() . '=1'); ?>"
                                           class="tbody-icon<?php echo $item->featured ? ' active' : ''; ?>"
                                           title="<?php echo Text::_($item->featured ? 'JUNFEATURE' : 'JFEATURE'); ?>">
                                            <span class="<?php echo $item->featured ? 'icon-color-featured icon-star' : 'icon-unfeatured'; ?>" aria-hidden="true"></span>
                                        </a>
                                    </td>
                                    <th scope="row">
                                        <a href="<?php echo Route::_('index.php?option=com_pmsnippets&task=snippet.edit&id=' . (int) $item->id); ?>">
                                            <?php echo $this->escape($item->title); ?>
                                        </a>
                                        <div class="small text-muted">
                                            <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                            | <?php echo $this->escape($item->category_title ?: Text::_('JNONE')); ?>
                                            | <?php echo $this->escape($contexts[$item->context_type] ?? $item->context_type); ?>
                                        </div>
                                    </th>
                                    <td class="d-none d-md-table-cell">
                                        <?php foreach (array_filter(explode(',', (string) $item->versions_csv)) as $ver) : ?>
                                            <span class="badge bg-secondary"><?php echo $this->escape(PmsnippetsHelper::getVersionShortLabel($ver)); ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <span class="badge bg-dark"><?php echo $this->escape($codeLangs[$item->code_language] ?? strtoupper($item->code_language)); ?></span>
                                    </td>
                                    <td class="small d-none d-lg-table-cell">
                                        <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <span class="badge bg-info"><?php echo (int) $item->copies_count; ?></span>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <?php echo (int) $item->hits; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo (int) $item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
