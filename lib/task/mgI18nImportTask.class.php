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
    $this->namespace = 'mgI18n';
    $this->name = 'xliff-import';
    $this->briefDescription = 'Import a symfony xliff catalogue into the database';

    $this->detailedDescription = <<<EOF
Import a symfony xliff catalogue into the database
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    
    $databaseManager = new sfDatabaseManager($this->configuration);
    
    foreach($arguments['source'] as $source)
    {
      $this->handleSource($arguments['application'], $source);
    }
    
  }
  
  protected function handleSource($application, $source)
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
    
    $table = Doctrine::getTable('mgI18nCatalogue');
    $catalogue_app = $application.'.'.$catalogue.'.'.$culture;

    foreach($merged as $source => $target)
    {
      $this->logSection('mgI18n', '  + '.$source .' => '. $target);
      $table->addMessage($catalogue_app, $source, $target);
    }
   
  }
}