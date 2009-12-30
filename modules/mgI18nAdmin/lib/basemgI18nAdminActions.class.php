<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2008 MenuGourmet 
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
class basemgI18nAdminActions extends sfActions
{
  public function executeGetTargets(sfWebRequest $request)
  {
    $this->forward404If(!sfConfig::get('mg_i18n_enabled'));
    
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
      
      $json['mg-i18n-target-'.$culture] = $trans_unit->target;
      
      
      unset($cultures[$culture]);
    }
    
    foreach($cultures as $code => $name)
    {
       $json['mg-i18n-target-'.$code] = '';
    }
    
    return $this->renderText(json_encode($json));
  }
  
  public function executeUpdateTargets(sfWebRequest $request)
  {

    $this->forward404If(!sfConfig::get('mg_i18n_enabled'));
    
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

      // allow to tweak the clear_cache method
      $event = $this->getContext()->getEventDispatcher()->notifyUntil(new sfEvent($params, 'mgI18n.clear_cache'));

      if(!$event->isProcessed())
      {
        chdir(sfConfig::get('sf_root_dir'));
        $cc = new sfCacheClearTask($this->getContext()->getEventDispatcher(), new sfFormatter);
        $cc->run(array(), array('--type=i18n'));
      }
    }
    
    return sfView::NONE;
    
  }

  public function executeGetMessagesByType(sfWebRequest $request)
  {
    // TODO : set this value in a settings
    $this->forward404If(!sfConfig::get('mg_i18n_enabled'));
    
    $valid_types = array('lib', 'application', 'ajax');

    $type = $request->getParameter('type');
    
    $this->forward404If(!in_array($type, $valid_types));
   
    $finder = sfFinder::type('file')
      ->ignore_version_control();

    if($type == 'lib')
    {
      $in = sfConfig::get('sf_root_dir');
      
      $finder
        ->discard('*actions.class.php')
        ->name('*.class.php')
      ;
    }
    else if($type == 'application')
    {
      $in = sfConfig::get('sf_root_dir');

      $finder
        ->name('*actions.class.php')
      ;
    }
    // untranslatable sentence, hard to find
    else if($type == 'ajax')
    {
      $event    = new sfEvent($this, 'mgI18nPlugin.assign_ajax_values');

      $this->getContext()->getEventDispatcher()->filter($event, array());

      $messages = $event->getReturnValue();
      $finder   = false;
    }
    else
    {
      $this->forward404();
    }

    if($finder)
    {
      $files = $finder->in($in);

      $php_extractor = new mgI18nPhpExtractor;

      $messages = array();
      foreach($files as $file)
      {
        $content  = file_get_contents($file);
        $messages = array_merge($messages, $php_extractor->extract($content));
      }
    }

    // now transform the output to be valid
    $valid_messages = array();
    foreach($messages as $message)
    {
      if(!isset($message['message']))
      {
        // no message, .... error in parsing
        continue;
      }
      $original_catalogue = ($message['catalogue'] ? $message['catalogue'] : 'messages');
   
      $catalogue = $this->getContext()->getConfiguration()->getApplication().'.'.$original_catalogue;
      
      if(!array_key_exists($catalogue, $valid_messages))
      {
        $valid_messages[$catalogue] = array();
      }

      $this->getContext()->getConfiguration()->loadHelpers(array('Text'));

      $hash = md5($message['message']);
      $valid_messages[$catalogue][$hash] = array(
        'source' => $message['message'],
        'target' => htmlentities(truncate_text(__($message['message'], null, $original_catalogue), 70)),
        'params' => isset($message['params']) ? $message['params'] : array(), // not fully implemented yet,
        'is_translated' => $message['message'] != __($message['message'], null, $original_catalogue)
      );
    }

    return $this->renderText(json_encode(array(
      'type' => $type,
      'messages' => $valid_messages
    )));

  }
}