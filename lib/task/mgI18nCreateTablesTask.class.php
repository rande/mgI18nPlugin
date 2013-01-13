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
class mgI18nCreateTablesTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
    ));
    
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
    ));
    
    $this->namespace = 'i18n';
    $this->name = 'mg-create-tables';
    $this->briefDescription = '[mgI18nPlugin] Create tables required by the plugin';

    $this->detailedDescription = <<<EOF
Create tables required by the plugin.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // retrieve the PDO object
    $manager  = new sfDatabaseManager($this->configuration);
    $database = $manager->getDatabase(sfConfig::get('app_mgI18nPlugin_connection'));
    $pdo = $database->getConnection();

    // get the driver name
    $driver = strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));

    // define SQLs
    switch ($driver)
    {
      case 'mysql':
        $sqls = array(
          sprintf("CREATE TABLE %s (cat_id BIGINT AUTO_INCREMENT, name VARCHAR(100), source_lang VARCHAR(100), target_lang VARCHAR(100), date_created BIGINT, date_modified BIGINT, author VARCHAR(255), PRIMARY KEY(cat_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;", mgI18nPluginConfiguration::getTableName('catalogue')),
          sprintf("CREATE TABLE %s (msg_id BIGINT AUTO_INCREMENT, cat_id BIGINT DEFAULT '1', source LONGTEXT, target LONGTEXT, comments LONGTEXT, date_added BIGINT DEFAULT 0, date_modified BIGINT DEFAULT 0, author VARCHAR(255), translated TINYINT(1), INDEX cat_id_idx (cat_id), PRIMARY KEY(msg_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;", mgI18nPluginConfiguration::getTableName('trans_unit')),
          sprintf("ALTER TABLE %s ADD FOREIGN KEY (cat_id) REFERENCES `%s` (cat_id);", mgI18nPluginConfiguration::getTableName('trans_unit'), mgI18nPluginConfiguration::getTableName('catalogue')),
        );
        break;
      case 'pgsql':
        $sqls = array(
          sprintf("CREATE TABLE %s (cat_id bigserial, name character varying(100), source_lang character varying(100), target_lang character varying(100), date_created bigint, date_modified bigint, author character varying(255), PRIMARY KEY (cat_id));", mgI18nPluginConfiguration::getTableName('catalogue')),
          sprintf("CREATE TABLE %s (msg_id bigserial, cat_id bigint DEFAULT 1, source text, target text, comments text, date_added bigint DEFAULT 0, date_modified bigint DEFAULT 0, author character varying(255), translated boolean, PRIMARY KEY (msg_id));", mgI18nPluginConfiguration::getTableName('trans_unit')),
          sprintf("ALTER TABLE %s ADD FOREIGN KEY (cat_id) REFERENCES %s (cat_id) ON UPDATE NO ACTION ON DELETE NO ACTION;", mgI18nPluginConfiguration::getTableName('trans_unit'), mgI18nPluginConfiguration::getTableName('catalogue')),
        );
        break;
      default:
        throw new sfException('Queries are not defined for "'.$driver.'" driver!');
        break;
    }

    $this->logSection('install', 'Create tables');

    // execute queries
    $pdo->exec('START TRANSACTION');
    
    foreach($sqls as $sql)
    {
      $pdo->exec($sql);
    }
    $pdo->exec('COMMIT');
    
    $this->logSection('install', 'done ...');
    $this->logSection('install', 'you can now import xliff files into the database');
  }
}