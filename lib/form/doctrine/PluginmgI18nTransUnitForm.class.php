<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2008 MenuGourmet 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 *
 * @package    mgI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
abstract class PluginmgI18nTransUnitForm extends BasemgI18nTransUnitForm
{
  public function configure()
  {
    unset($this['date_created']);
    unset($this['date_modified']);
    unset($this['id']);
    
    $this->widgetSchema['source']->setAttribute('readonly', 'true');
  }

  protected function doSave($con = null)
  {
    parent::doSave($con);
 
    $catalogue = $this->getObject()->mgI18nCatalogue;
    
    Doctrine::getTable('mgI18nCatalogue')
      ->clearCache($catalogue->getName(), $catalogue->getLanguage());
    
  }
}