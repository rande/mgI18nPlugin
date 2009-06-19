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

<?php $actionName = sfContext::getInstance()->getActionStack()->getLastEntry()->getActionName() ?>
<div class="sw-base-left-actions">
  <ul>
    <li><?php echo link_to(__('link_list', null, 'i18n'),'mgI18nAdmin/index') ?></li>
    <li><?php echo link_to(__('link_create_i18n', null, 'i18n'),'mgI18nAdmin/create') ?></li>
  </ul>
</div>