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

<div class="sw-base-filters">
  <h3>Filters</h3>
  <form action="<?php echo url_for('mgI18nAdmin/index') ?>" method="get" />
    <?php echo $i18n_trans_unitList ?>
      
    <input type="submit" name="filters[filter]" value="filter" />
    <input type="submit" name="filters[reset]" value="reset" />
  </form>
</div>

<h1>I18n List</h1>

<div class="sw-base-admin-list">
  <table class="sw-base-admin-table-standard">
    <thead>
      <tr>
        <th>Msg</th>
        <th>Cat</th>
        <th>Source</th>
        <th>Target</th>
        <th>Comments</th>
        <th>Author</th>
        <th>Translated</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($i18n_trans_unitList->getResults() as $i18n_trans_unit): ?>
      <tr>
        <td><a href="<?php echo url_for('mgI18nAdmin/edit?msg_id='.$i18n_trans_unit['msg_id']) ?>"><?php echo $i18n_trans_unit['msg_id'] ?></a></td>
        <td><?php echo $i18n_trans_unit['mgI18nCatalogue']['name'] ?></td>
        <td><?php echo $i18n_trans_unit['source'] ?></td>
        <td><?php echo $i18n_trans_unit['target'] ?></td>
        <td><?php echo $i18n_trans_unit['comments'] ?></td>
        <td><?php echo $i18n_trans_unit['author'] ?></td>
        <td><?php echo $i18n_trans_unit['translated'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfooter>
      <tr>
        <td colspan="7" class="sw-base-admin-table-pager">
          <?php echo sw_pager_navigation($i18n_trans_unitList, 'mgI18nAdmin/index') ?>
        </td>
      </tr>
    </tfooter>
  </table>
</div>

<a href="<?php echo url_for('mgI18nAdmin/create') ?>">Create</a>
