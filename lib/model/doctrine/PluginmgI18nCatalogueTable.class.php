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


class PluginmgI18nCatalogueTable extends Doctrine_Table
{

  public function clearCache($variant, $language)
  {
    // 2 folders due to dimension
    $globs = array(
      sfConfig::get('sf_cache_dir').'/*/*/*/i18n/'.$variant.'/'.$language.'.cache',
      sfConfig::get('sf_cache_dir').'/*/*/i18n/'.$variant.'/'.$language.'.cache', 
    );
    

    foreach($globs as $glob) 
    {
      sfToolkit::clearGlob($glob);
    }
  }
  
  public function addMessage($name_catalogue, $source, $target)
  {
    $catalogue = $this->findOneByName($name_catalogue);
    
    if(!$catalogue instanceof mgI18nCatalogue)
    {
      $catalogue = new mgI18nCatalogue;
      $catalogue->setName($name_catalogue);
      $catalogue->save();
    }
    
    $trans_unit = new mgI18nTransUnit;
    $trans_unit->setCatId($catalogue->getCatId());
    $trans_unit->setSource($source);
    $trans_unit->setTarget($target);
    $trans_unit->setTranslated(true);
    $trans_unit->setDateAdded($time);
    $trans_unit->setDateModified($time);
    $trans_unit->save();
  }
  
  public function saveMessages($to_analyse, $culture)
  {
    $tc = $this;
    $tt = Doctrine::getTable('mgI18nTransUnit');
    $time = time();

    foreach($to_analyse as $catalogue => $messages)
    {
      $variant = $catalogue.'.'.$culture;
      $catalogue = $tc->createQuery()->where('name = ?', $variant)->fetchOne();

      if(!$catalogue)
      {
        $catalogue = new mgI18nCatalogue;
        $catalogue->setDateCreated($time);
        $catalogue->setDateModified($time);
        $catalogue->setAuthor('mgI18NPlugin tool');
        $catalogue->setName($variant);
        $catalogue->save();
      }

      if(is_array($messages) && count($messages) > 0)
      {
        foreach($messages as $message)
        {
          $trans_unit = $tt
            ->createQuery()
            ->where('cat_id = ? AND source = ?', array($catalogue->getCatId(), $message))
            ->fetchOne();

          if($trans_unit)
          {
            continue;
          }

          $trans_unit = new mgI18nTransUnit;
          $trans_unit->setCatId($catalogue->getCatId());
          $trans_unit->setTranslated(false);
          $trans_unit->setDateAdded($time);
          $trans_unit->setDateModified($time);
          $trans_unit->setAuthor('mgI18N Tool');
          $trans_unit->setSource($message);
          $trans_unit->setComments('to be translated');
          try
          {
            $trans_unit->save();
          }
          catch(Exception $e)
          {
            var_dump($e->getMessage());
          }
        }
      }
    }
  }
}