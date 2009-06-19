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


abstract class PluginmgI18nTransUnit extends BasemgI18nTransUnit
{
  public function preSave($event)
  {
    $modified = $this->getModified();
    if(count($modified) > 0)
    {
      $time = time();

      if(!in_array('date_added', $modified))
      {
        $this->setDateAdded($time);
      }

      if(!in_array('date_modified', $modified))
      {
        $this->setDateModified($time);
      }
    }
  }
}