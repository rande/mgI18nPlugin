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

/**
 *
 * @package    mgI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class mgI18nPluginPanel extends sfWebDebugPanel
{
  public function getTitle()
  {
    
    return 'Translations';
  }
  
  public function getPanelTitle()
  {
    
    return 'Translations';
  }
  
  public function getPanelContent()
  {
    $i18n = sfContext::getInstance()->getI18N();
    
    $json = json_encode($i18n->getRequestedMessages());

    $textareas = '';
    foreach(sfConfig::get('app_mgI18nPlugin_cultures_available') as $code => $name)
    {
      $textareas .= sprintf(
        "<strong>%s</strong><br /><textarea style='width: 400px; height: 60px' name='targets[%s]' id='_mg_i18n_target_%s'></textarea><br />",
        $name,
        $code,
        $code
      );
    }
    
    return sprintf('<div id="sfWebDebugDatabaseLogs">
      <script>
        var _mg_i18n_messages = %s;
        var _mg_i18n_lang     = %s;
      </script>
      <script type="text/javascript" src="/mgI18nPlugin/js/mgI18n.js"></script>
      <div style="float: left; width: 600px; height: 400px; overflow: scroll">
        <table>
          <thead>
            <tr>
              <th>Catalogue</th>
              <th>Source</th>
              <th style="width: 300px">Target</th>
            </tr>
          </thead>
          <tbody id="_mg_i18n_tbody"></tbody>
        </table>
      </div>
      <style>
        
        #_mg_i18n_tbody td._mg_i18_td_selected {
          background: #5B482F;
          color: white;
          padding: 2px;
        }
        
        #_mg_i18n_tbody td._mg_i18_td_unselected {
          padding: 2px;
        }
      </style>
      <div style="float: left; padding-left: 10px;">
        <h2>Instructions</h2>
        <p>
          1. Click on one item on the left table to see its current translations. <br /> 
          2. Then you are free to edit the different translations to meet your requirements. <br />
          3. <strong>save your translations !</strong>
        </p>
        <h2>Information</h2>
        <em>Once you have saved the translation, you can <a href="javascript:void(0)" onclick="document.location.reload()" ><strong>reload</strong></a> this page to see your changes</em><br /><br />
        <h2>Translations</h2>
        <strong class="_mg_i18n_parameters">parameters : </strong>
        <span class="_mg_i18n_parameters" id="_mg_i18n_parameters_text"></span>
        <br class="_mg_i18n_parameters" /><br class="_mg_i18n_parameters" />
        
        <form action="/backend.php/mgI18nAdmin/panelUpdate" id="_mg_i18n_form">
          <input type="hidden" readonly="true" name="catalogue" id="_mg_i18n_catalogue" value="" />
          <input type="hidden" readonly="true" name="source" id="_mg_i18n_source" value="" />
          %s
          <br />
          <img src="/mgI18nPlugin/images/tiny_red.gif" id="_mg_i18n_loading"/>
          <input type="submit" name="Save" id="_mg_i18n_submit" />
        </form>
      </div>
    </div>', $json, json_encode(sfConfig::get('app_mgI18nPlugin_cultures_available')), $textareas);
  }
  
  static public function listenToAddPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel('mg.mg_i18n_plugin', new self($event->getSubject()));
  }
}