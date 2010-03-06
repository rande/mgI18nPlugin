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
class mgI18nImportTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('source', sfCommandArgument::REQUIRED ^ sfCommandArgument::IS_ARRAY, 'The xliff file location(s) of the remote'),
    ));
    
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
    ));
    
    $this->aliases = array('mg-i18n-xliff-import');
    $this->namespace = 'i18n';
    $this->name = 'mg-xliff-import';
    $this->briefDescription = '[mgI18nPlugin] Import a symfony xliff catalogue into the database';

    $this->detailedDescription = <<<EOF
Import a symfony xliff catalogue into the database
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    
    $message_source = new sfMessageSource_mgMySQL(sfConfig::get('app_mgI18nPlugin_connection'));
    
    foreach($arguments['source'] as $source)
    {
      $this->handleSource($message_source, $arguments['application'], $source);
    }
    
  }
  
  protected function handleSource($message_source, $application, $source)
  {
    
    $culture = null;
    
    $this->logSection('mgI18n', 'Analysing '.$source);
    
    if(is_file($source))
    {
      $info =  pathinfo($source);
      
      $name_info = explode('.', $info['basename']);
      
      if(count($name_info) < 3 || $info['extension'] != 'xml')
      {
        
        $this->logSection('mgI18n', 'Wrong file name format : path/to/file.LANG.xml');
        return;
      }
      
      $culture = $name_info[1];
      $catalogue = $name_info[0];
      
      $source = $info['dirname'];
    }
    else
    {
      $this->logSection('mgI18n', 'The source must be a file');
      return;
    }

    $source = sfMessageSource::factory('XLIFF', $source);
    $source->setCulture($culture);
    $source->load($catalogue);
     
    $merged = array();
    foreach($source->read() as $variants)
    {
      foreach($variants as $source => $target)
      {
        if(!array_key_exists($source, $merged))
        {
          $merged[$source] = $target[0];
        }
      }
    }

    $catalogue_app = $application.'.'.$catalogue.'.'.$culture;

    $messages = $message_source->loadData($catalogue_app);
    
    foreach($merged as $source => $target)
    {
      if(isset($messages[$source]))
      {
        $this->logSection('mgI18n', '  ~ update : '.$source .' => '. $target);
        $message_source->update($source, $target, '', $catalogue_app);
      }
      else
      {
        $this->logSection('mgI18n', '  + insert : '.$source .' => '. $target);
        $message_source->insert($source, $target, '', $catalogue_app);
      }
    }
  }
}