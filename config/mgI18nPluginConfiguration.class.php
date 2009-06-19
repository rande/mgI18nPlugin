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


class mgI18nPluginConfiguration extends sfPluginConfiguration
{

  public function initialize()
  {

    if (sfConfig::get('sf_web_debug'))
    {

      $this->dispatcher->connect('debug.web.load_panels', array('mgI18nPluginPanel', 'listenToAddPanelEvent'));
    }
  }
}