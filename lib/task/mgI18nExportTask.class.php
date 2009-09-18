<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2008 Qarmaq 
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
class mgI18nExportTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('source', sfCommandArgument::REQUIRED, 'The xliff file location of the remote'),
    ));
    
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
    ));
    
    $this->aliases = array('mg-i18n-xliff-export');
    $this->namespace = 'mgI18n';
    $this->name = 'xliff-export';
    $this->briefDescription = 'Export a database catalogue into a xliff file';

    $this->detailedDescription = <<<EOF
Export a database catalogue into a xliff file
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {

    $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], $options['env'], true);
    sfContext::createInstance($configuration);
    
    // TODO
    
    throw new sfException('not implemented yet');
  }
}