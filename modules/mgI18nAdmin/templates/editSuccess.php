<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2008 MenuGourmet 
 *
 * Author : Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>

<?php $i18n_trans_unit = $form->getObject() ?>

<?php include_partial('actions', array('i18n_trans_unit' => $i18n_trans_unit)) ?>

<div class="sw-base-container-column">
  
  <h1><?php echo $form->isNew() ? 'New' : 'Edit' ?> I18n</h1>
  
  <form action="<?php echo url_for('mgI18nAdmin/update'.(!$form->isNew() ? '?msg_id='.$i18n_trans_unit['msg_id'] : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
    <table>
      <tfoot>
        <tr>
          <td colspan="2">
            &nbsp;<a href="<?php echo url_for('mgI18nAdmin/index') ?>">Cancel</a>
            <?php if (!$form->isNew()): ?>
              &nbsp;<?php echo link_to('Delete', 'mgI18nAdmin/delete?msg_id='.$i18n_trans_unit['msg_id'], array('post' => true, 'confirm' => 'Are you sure?')) ?>
            <?php endif; ?>
            <input type="submit" value="Save" />
          </td>
        </tr>
      </tfoot>
      <tbody>
        <?php echo $form->renderGlobalErrors() ?>
        <tr>
          <th><label for="i18n_trans_unit_cat_id">Cat id</label></th>
          <td>
            <?php echo $form['cat_id']->renderError() ?>
            <?php echo $form['cat_id'] ?>
          </td>
        </tr>
        <tr>
          <th><label for="i18n_trans_unit_source">Source</label></th>
          <td>
            <?php echo $form['source']->renderError() ?>
            <?php echo $form['source'] ?>
          </td>
        </tr>
        <tr>
          <th><label for="i18n_trans_unit_target">Target</label></th>
          <td>
            <?php echo $form['target']->renderError() ?>
            <?php echo $form['target'] ?>
          </td>
        </tr>
        <tr>
          <th><label for="i18n_trans_unit_comments">Comments</label></th>
          <td>
            <?php echo $form['comments']->renderError() ?>
            <?php echo $form['comments'] ?>
          </td>
        </tr>
        <tr>
          <th><label for="i18n_trans_unit_author">Author</label></th>
          <td>
            <?php echo $form['author']->renderError() ?>
            <?php echo $form['author'] ?>
          </td>
        </tr>
        <tr>
          <th><label for="i18n_trans_unit_translated">Translated</label></th>
          <td>
            <?php echo $form['translated']->renderError() ?>
            <?php echo $form['translated'] ?>
  
          <?php echo $form['msg_id'] ?>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>