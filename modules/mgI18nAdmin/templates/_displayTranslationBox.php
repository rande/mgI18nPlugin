<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    mgI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
 ?>

<!--
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" ></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js" ></script>

<link rel="stylesheet" type="text/css" media="screen" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/redmond/jquery-ui.css" />
-->

<script type="text/javascript" src="/mgI18nPlugin/js/gui.js" ></script>



<style>

  div#mg-i18n-dialog {
    display: none;
    position: absolute;
    font-size: 10px;
  }

  div#mg-i18n-dialog td {
    white-space: nowrap;
  }

  div#mg-i18n-dialog h2 {
    padding: 2px;
  }

  input.mg-i18n-current-page-search {
    width: 100px;
  }

  div#mg-i18n-dialog div.ui-tabs div.ui-tabs-panel {
    padding: 0;
  }

  div.mg-i18n-container, div#mg-i18n-dialog .ui-widget {
    font-size: 10px;
  }

  #mg-i18n-loading-box {
    width: 650px;
    height: 350px;
    text-align: center;
    line-height: 40px;
  }

  #mg-i18n-left-box {
    float: left;
    width: 55%;
  }

  #mg-i18n-right-box {
    float: left;
    width: 40%;
  }

  textarea.mg-i18n-translation-input {
    width: 95%;
    margin: 2%;
    height: 45px;
    scroll: auto;
    padding: 2px;
  }

  div.mg-i18n-messages {
    height: 300px;
    overflow: scroll;
  }

  div.mg-i18n-messages td._mg_i18_td_selected {
    background: #5B482F;
    color: white;
    padding: 2px;
  }

  div.mg-i18n-messages td._mg_i18_td_unselected {
    padding: 2px;
  }

  textarea.mg-i18n-translation-input {
    
  }
</style>


<div id="mg-i18n-dialog" class="ui-widget">
  <div id="mg-i18n-on-top-box">
    <h2 class="ui-widget-header ui-corner-all"><?php echo __('title_translation', null, 'mgI18nAdmin') ?></h2>
  </div>

  <div id="mg-i18n-container" class="ui-widget ui-widget-content ui-corner-all">
    <div id="mg-i18n-left-box">
      <ul>
        <li><a href="#mg-i18n-panel-page"><?php echo __('tabs_translation_current_page', null, 'mgI18nAdmin') ?></a></li>
        <li><a href="#mg-i18n-panel-ajax"><?php echo __('tabs_translation_current_ajax', null, 'mgI18nAdmin') ?></a></li>
        <li><a href="#mg-i18n-panel-application"><?php echo __('tabs_translation_application', null, 'mgI18nAdmin') ?></a></li>
        <li><a href="#mg-i18n-panel-lib"><?php echo __('tabs_translation_lib', null, 'mgI18nAdmin') ?></a></li>
        <li><a href="#mg-i18n-panel-help"><?php echo __('tabs_translation_help', null, 'mgI18nAdmin') ?></a></li>
      </ul>

      <?php foreach(array('page', 'ajax', 'application', 'lib') as $type): ?>
        <div id="mg-i18n-panel-<?php echo $type ?>" rel="<?php echo $type ?>">
          <div class="mg-i18n-toolbar">
            <input type="checkbox" class="mg-i18n-hide-translated" />
            <label for="mg-18n-current-page-hide-translated"><?php echo __('label_hide_translated_page', null, 'mgI18nAdmin') ?></label>

            <?php echo __('label_filter_list', null, 'mgI18nAdmin') ?>
            <input type="text" class="mg-i18n-current-page-search" />
          </div>
          <div class="mg-i18n-messages">
            <table>
              <thead>
                <tr>
                  <td class="mg-i18n-td-catalogue"><?php echo __('header_catalogue', null, 'mgI18nAdmin') ?></td>
                  <td class="mg-i18n-td-targets"><?php echo __('header_targets', null, 'mgI18nAdmin') ?></td>
                </tr>
              </thead>
              <tbody />
              <tfoot />
            </table>
          </div>
        </div>
      <?php endforeach ?>
      
      <div id="mg-i18n-panel-help">
        <h3><?php echo __('title_instruction', null, 'mgI18nAdmin') ?></h3>
        <p><?php echo __('message_instruction', null, 'mgI18nAdmin') ?></p>
      </div>
    </div>

    <div id="mg-i18n-right-box">
      <strong class="mg-i18n-parameters"><?php echo __('label_parameters', null, 'mgI18nAdmin') ?></strong>
      <span class="mg-i18n-parameters" id="mg-i18n-parameters-text"></span>
      <br class="mg-i18n-parameters" /><br class="mg-i18n-parameters" />

      <form action="<?php echo url_for('@mg_i18n_update') ?>" id="mg-i18n-form-update">
        <input type="hidden" readonly="true" name="catalogue" id="mg-i18n-catalogue" value="" />
        <input type="hidden" readonly="true" name="source" id="mg-i18n-source" value="" />

        <div class="mg-i18-translations">
          <?php foreach(sfConfig::get('app_mgI18nPlugin_cultures_available') as $code => $name): ?>
            <div class="mg-i18n-translation">
              <strong><?php echo $name ?></strong><br />
              <textarea class="mg-i18n-translation-input" name='targets[<?php echo $code ?>]' id='mg-i18n-target-<?php echo $code ?>'></textarea>
            </div>
          <?php endforeach; ?>
        </div>

        <div>
          <img src="/mgI18nPlugin/images/tiny_red.gif" id="mg-i18n-loading"/>
          <input type="submit" value="<?php echo __('btn_save_translation', null, 'mgI18nAdmin') ?>" id="mg-i18n-submit" />
        </div>
      </form>
    </div>
    <div style="clear:both"></div>
  </div>

  <div id="mg-i18n-loading-box" class="ui-widget ui-widget-content ui-corner-all">
    <?php echo __('message_loading', null, 'mgI18nAdmin') ?> <br />
    <img src="/mgI18nPlugin/images/tiny_red.gif"/>
  </div>
  
</div>

<script>

if(typeof jQuery != 'undefined')
{
  
  jQuery(window).bind('load', function() {
    mgI18nPlugin.instance = new mgI18nPlugin({
      url_translation: '<?php echo url_for('mg_i18n_get_targets') ?>',
      url_messages: '<?php echo url_for('@mg_i18n_get_messages?type=MESSAGE_TYPE') ?>'
    });
  });
}
else
{

  alert('Please add jQuery UI to see the translation tools');
}

</script>