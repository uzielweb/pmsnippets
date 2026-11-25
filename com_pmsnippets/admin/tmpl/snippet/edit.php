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
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \PontoMega\Component\Pmsnippets\Administrator\View\Snippet\HtmlView $this */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
?>
<form action="<?php echo Route::_('index.php?option=com_pmsnippets&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post"
      name="adminForm"
      id="snippet-form"
      aria-label="<?php echo Text::_('COM_PMSNIPPETS_SNIPPET_EDIT'); ?>"
      class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'codeblocks', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'codeblocks', Text::_('COM_PMSNIPPETS_TAB_CODE_BLOCKS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('summary'); ?>
                <?php echo $this->form->renderField('code_blocks'); ?>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderField('state'); ?>
                <?php echo $this->form->renderField('featured'); ?>
                <?php echo $this->form->renderField('catid'); ?>
                <?php echo $this->form->renderField('code_language'); ?>
                <?php echo $this->form->renderField('context_type'); ?>
                <?php echo $this->form->renderField('difficulty'); ?>
                <?php echo $this->form->renderField('tags'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('language'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_PMSNIPPETS_TAB_DETAILS')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->form->renderField('description'); ?>
                <?php echo $this->form->renderField('source_url'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_PMSNIPPETS_TAB_PUBLISHING')); ?>
        <div class="row">
            <div class="col-lg-6">
                <?php echo $this->form->renderField('created'); ?>
                <?php echo $this->form->renderField('created_by'); ?>
                <?php echo $this->form->renderField('publish_up'); ?>
                <?php echo $this->form->renderField('publish_down'); ?>
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('hits'); ?>
                <?php echo $this->form->renderField('copies_count'); ?>
                <?php echo $this->form->renderField('useful_count'); ?>
            </div>
            <div class="col-lg-6">
                <?php echo $this->form->renderField('metadesc'); ?>
                <?php echo $this->form->renderField('metakey'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if (Associations::isEnabled()) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
            <fieldset id="fieldset-associations" class="options-form">
                <legend><?php echo Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS'); ?></legend>
                <?php echo LayoutHelper::render('joomla.edit.associations', $this); ?>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
