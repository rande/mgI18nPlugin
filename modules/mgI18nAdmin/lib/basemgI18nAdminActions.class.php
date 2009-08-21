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

class basemgI18nAdminActions extends sfActions
{
 public function executeIndex(sfWebRequest $request)
  {
    $this->i18n_trans_unitList = new mgI18NDatagrid(
      $request->getParameter('filters', array()), 
      array(
        'page'      => $request->getParameter('page'),
        'per_page'  => 25
      )
    ); 
  }

  public function executeGetTargets(sfWebRequest $request)
  {
    $catalogue = $request->getParameter('catalogue');
    $source    = $request->getParameter('source');
    
    $catalogues = array();
    
    $cultures = sfConfig::get('app_mgI18nPlugin_cultures_available');
    
    foreach( $cultures as $code => $name)
    {
      $catalogues[] = $catalogue.'.'.$code; 
    }
    
    $trans_units = Doctrine::getTable('mgI18nTransUnit')
      ->createQuery('tu')
      ->leftJoin('tu.mgI18nCatalogue tc')
      ->select('*')
      ->whereIn('tc.name', $catalogues)
      ->addWhere('tu.source = ?', $source)
      ->execute();
    
    $json = array();
    
    
    foreach($trans_units as $trans_unit)
    {
      $name_catalogue = $trans_unit->mgI18nCatalogue->name;
      $culture = $trans_unit->mgI18nCatalogue->getLanguage();
      
      $json['_mg_i18n_target_'.$culture] = $trans_unit->target;
      
      
      unset($cultures[$culture]);
    }
    
    foreach($cultures as $code => $name)
    {
       $json['_mg_i18n_target_'.$code] = '';
    }
    
    return $this->renderText(json_encode($json));
  }
  
  public function executeDisplayAjaxMessages(sfWebRequest $request)
  {
    // OVERWRITE THIS METHOD IN YOUR MAIN APPLICATION
  }
  
  public function executeUpdateTargets(sfWebRequest $request)
  {
    
    $catalogue = $request->getParameter('catalogue');
    $source    = $request->getParameter('source');
    $targets   = $request->getParameter('targets');
    
    $params = array(
      'catalogue' => $catalogue,
      'source'    => $source,
      'targets'   => $targets
    );
    
    $form = new mgI18nTargetsForm;
    
    $form->bind($params);
    if($form->isValid())
    {
      $form->save();
    }
    
    return sfView::NONE;
    
  }

  public function executeParseActionFiles(sfWebRequest $request)
  {

    $app_dir = sfConfig::get('sf_app_dir');

    $files = sfFinder::type('file')->ignore_version_control()->name('*actions.class.php')->in($app_dir);

    $all_results = array();
    
    foreach($files as $file)
    {
      $lines = file($file);

      $phrases = array();

      $ereg = "/(.*)__\(([^\)]*)\)(.*)/";
      foreach($lines as $line)
      {
        if(preg_match($ereg, $line, $results))
        {
          $params = explode(',', $results[2]);

          // $phrase = ''tototo'' or '"tototo"'
          
          if(count($params) < 3)
          {
            // something is wrong
            $error     = true;
            $phrase    = false;
            $catalogue = false;
          }
          else
          {
            $phrase    = substr(trim($params[0]), 1, -1);
            $catalogue = substr(trim($params[2]), 1, -1);
            $error     = false;
          }
          
          

          $phrases[] = array(
            'phrase'    => $phrase,
            'catalogue' => $catalogue,
            'line'      => $line,
            'error'     => $error,
          );
        }
        
      }

      if(count($phrases) == 0)
      {
        continue;
      }

      $all_results[$file] = $phrases;
    }

    $this->all_results = $all_results;
    
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->form = new mgI18nTransUnitForm();

    $this->setTemplate('edit');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->form = $this->geti18nTransUnitForm($request->getParameter('msg_id'));
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod('post'));

    $this->form = $this->geti18nTransUnitForm($request->getParameter('msg_id'));

    $this->form->bind($request->getParameter('i18n_trans_unit'));
    if ($this->form->isValid())
    {
      $i18n_trans_unit = $this->form->save();

      //$this->redirect('i18n/edit?msg_id='.$i18n_trans_unit['msg_id']);
      $this->redirect('mgI18nAdmin/index');
    }

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
    $this->forward404Unless($i18n_trans_unit = $this->geti18nTransUnitById($request->getParameter('msg_id')));

    $i18n_trans_unit->delete();

    $this->redirect('mgI18nAdmin/index');
  }
  
  private function geti18nTransUnitTable()
  {
    return Doctrine::getTable('mgI18nTransUnit');
  }
  
  private function geti18nTransUnitById($id)
  {
    return $this->geti18nTransUnitTable()->find($id);
  }
  
  private function geti18nTransUnitForm($id)
  {
    $i18n_trans_unit = $this->geti18nTransUnitById($id);
    
    if ($i18n_trans_unit instanceof mgI18nTransUnit)
    {
      return new mgI18nTransUnitForm($i18n_trans_unit);
    }
    else
    {
      return new mgI18nTransUnitForm();
    }
  }
}