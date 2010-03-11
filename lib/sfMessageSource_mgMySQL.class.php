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
 * @package    mgI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class sfMessageSource_mgMySQL extends sfMessageSource
{
  protected 
    $informations  = null,
    $application   = null, // the application name used
    $pdo           = null  // the PDO connection
  ;

  /**
   * The original sfMessageSource_MySQL runs too many query on the database server
   * So I have added a new loadInformations methods to preload information
   *
   * @param unknown_type $source
   */
  function __construct($source)
  {
    $this->source = (string) $source;
    $this->pdo    = $this->connect();
    
    $this->loadInformations();
  }

  public function getConnection()
  {
    
    return $this->pdo;
  }
  
  /**
   * This code is very bad ... don't do that at home
   *
   */
  public function connect()
  {
    static $connection;
    
    // try to get the PDO connection object from the sfContext
    if(sfContext::hasInstance())
    {
      
      return sfContext::getInstance()->getDatabaseConnection($this->source);
    }

    // try to get the PDO connection object from the configuration
    if($connection == null)
    {
      $configuration  = sfProjectConfiguration::getActive();
      $manager        = new sfDatabaseManager($configuration);
      $database       = $manager->getDatabase($this->source);
      $connection     = $database->getConnection();
    }

    return $connection;
  }
  
  
  public function load($catalogue = null)
  {
    if($catalogue == null || strlen($catalogue) == 0)
    {
      $catalogue = 'messages';
    }

    $catalogue = $this->getApplicationName().'.'.$catalogue;

    return parent::load($catalogue);
  }

  /**
   * preload catalogue information
   *
   */
  public function loadInformations()
  {

    $stm = $this->pdo->prepare("SELECT cat_id, name, date_modified FROM catalogue");
    $stm->execute();

    $this->informations = array();
    foreach($stm->fetchAll(PDO::FETCH_NUM) as $row)
    {
      $this->informations[$row[1]] = array('cat_id' => $row[0], 'date_modified' => $row[2]);
    }
  }
  
  /**
   * Gets all the variants of a particular catalogue.
   *
   * @param string $catalogue catalogue name
   * @return array list of all variants for this catalogue.
   */
  public function getCatalogueList($catalogue)
  {
    $variants = explode('_', $this->culture);

    $catalogues = array($catalogue);

    $variant = null;

    for ($i = 0, $max = count($variants); $i < $max; $i++)
    {
      if (strlen($variants[$i]) > 0)
      {
        $variant .= $variant ? '_'.$variants[$i] : $variants[$i];
        $catalogues[] = $catalogue.'.'.$variant;
      }
    }

    return array_reverse($catalogues);
  }

  public function appendRequestedMessage($message_information, $catalogue)
  {
    // initialize parameters
    if(!array_key_exists($catalogue, $this->untranslated))
    {
      $this->untranslated[$catalogue] = array();
    }
    
    $this->untranslated[$catalogue][md5($message_information['source'])] = $message_information;
  }
  
  /**
   * Adds a untranslated message to the source. Need to call save()
   * to save the messages to source.
   *
   * @param string $message message to add
   */
  public function append($message)
  {
    
  }
  
  public function getRequestedMessages()
  {

    return $this->untranslated;
  }
  
  /**
   * @see sfMessageSource_MySQL
   *
   */
  protected function getLastModified($source)
  {
     
    if(array_key_exists($source, $this->informations))
    {

      return $this->informations[$source]['date_modified'];
    }

    return 0;
  }

  /**
   * @see sfMessageSource_MySQL
   *
   */
  public function isValidSource($variant)
  {

    return array_key_exists($variant, $this->informations);
  }

  
  /**
   * @see sfMessageSource_MySQL
   *
   */
  protected function getCatalogueDetails($catalogue = 'messages')
  {
    if (empty($catalogue))
    {
      $catalogue = 'messages';
    }

    $variant = $catalogue.'.'.$this->culture;

    if(!array_key_exists($variant, $this->informations))
    {
      return false;
    }

    $cat_id = $this->informations[$variant]['cat_id'];

    // first get the catalogue ID
    
    $stm = $this->pdo->prepare("SELECT COUNT(*) FROM trans_unit WHERE cat_id = ?");
    $stm->execute(array($cat_id));
    
    $val = $stm->fetchAll(PDO::FETCH_NUM);
    
    $count = 0;
    if(count($val) > 0)
    {
      $count = $val[0][0];
    }

    return array($cat_id, $variant, $count);
  }

  /**
   * @see sfMessageSource_MySQL
   *
   */
  protected function updateCatalogueTime($cat_id, $variant)
  {
    $time = time();

    $stm = $this->pdo->prepare("UPDATE catalogue SET date_modified = ? WHERE cat_id = ?");
    $stm->execute(array($time, $cat_id));
    
    if(array_key_exists($variant, $this->informations))
    {

      $this->informations[$variant]['date_modified'] = $time;
    }

    if ($this->cache)
    {
      $this->cache->remove($variant.':'.$this->culture);
    }

    return $result;
  }
  
  /**
   * Gets an array of messages for a particular catalogue and cultural variant.
   *
   * @param string $variant the catalogue name + variant
   * @return array translation messages.
   */
  public function &loadData($variant)
  {

    $stm = $this->pdo->prepare(
      "SELECT t.msg_id, t.source, t.target, t.comments
        FROM trans_unit t, catalogue c
        WHERE c.cat_id =  t.cat_id
          AND c.name = ?
        ORDER BY t.msg_id ASC"
    );

    $stm->execute(array($variant));
    
    $result = array();

    foreach($stm->fetchAll(PDO::FETCH_NUM) as $row)
    {
      $source = $row[1];
      $result[$source][] = $row[2]; //target
      $result[$source][] = $row[0]; //id
      $result[$source][] = $row[3]; //comments
    }

    return $result;
  }
  
  public function getApplicationName()
  {
    
    return sfConfig::get('mg_i18n_global_application', sfProjectConfiguration::getActive()->getApplication());
  }
  
  /**
   * Returns a list of catalogue as key and all it variants as value.
   *
   * @return array list of catalogues 
   */
  function catalogues()
  {

    $stm = $this->pdo->prepare('SELECT name FROM catalogue ORDER BY name');
    $stm->execute();
    
    $result = array();
    
    foreach($stm->fetchAll(PDO::FETCH_NUM) as $row)
    {
      $details = explode('.', $row[0]);
      array_shift($details);
      
      if (!isset($details[1]))
      {
        $details[1] = null;
      }

      $result[] = $details;
    }

    return $result;
  }
  
  /**
   * return the catalogue id
   *
   * @param string $catalogue the catalogue name
   * @param boolean $force_create create the catalogue if not presents into the database
   */
  public function getCatalogueId($catalogue, $force_create = false)
  {
    
    if(isset($this->informations[$catalogue]))
    {
      
      return $this->informations[$catalogue]['cat_id'];
    }
    
    $date = strtotime('now');
     
    $catalogues = array();
    if($force_create)
    {
      $insert_catalogue_stm  = $this->pdo->prepare("INSERT INTO catalogue (name, date_created) VALUES (?, ?)");
      $insert_catalogue_stm->execute(array($catalogue, $date));
      
      $select_catalogue_stm = $this->pdo->prepare("SELECT cat_id FROM catalogue WHERE name = ?");
      $select_catalogue_stm->execute(array($catalogue));
      $catalogues = $select_catalogue_stm->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $cat_id = count($catalogues) > 0 ? $catalogues[0]['cat_id'] : false;
    
    return $this->informations[$catalogue]['cat_id'] = $cat_id;
  }

  /**
   * Saves the list of untranslated blocks to the translation source. 
   * If the translation was not found, you should add those
   * strings to the translation source via the <b>append()</b> method.
   *
   * @param string $catalogue not used with this class
   * @return boolean true if saved successfuly, false otherwise.
   */
  function save($catalogue = 'messages')
  {
    $select_message_stm  = $this->pdo->prepare("SELECT * FROM trans_unit WHERE source = ? AND cat_id = ? LIMIT 1");
    $insert_message_stm  = $this->pdo->prepare("INSERT INTO trans_unit (cat_id, source, target, date_modified) VALUES (?, ?, ?, ?)");
    
    $date       = strtotime('now');
    $catalogues = $this->getRequestedMessages();
    $in_prepare = array();
    foreach($catalogues as $catalogue => $messages)
    {
  
      if(!is_array($messages) || count($messages) == 0)
      {
        
        continue;
      }
      
      $variant = $catalogue.'.'.$this->culture;
      $cat_id = $this->getCatalogueId($variant, true);
      
      $in_prepare[$cat_id] = '?';
      
      foreach($messages as $message)
      {
        $select_message_stm->execute(array($message['source'], $cat_id));
        $trans_unit = $select_message_stm->fetchAll(PDO::FETCH_ASSOC);

        if(count($trans_unit) == 1)
        {
          
          continue;
        }

        $insert_message_stm->execute(array($cat_id, $message['source'], $message['source'], $date));
      }
    }
    
    if(count($in_prepare) > 0)
    {
      $update_stm = $this->pdo->prepare(sprintf("UPDATE catalogue SET date_modified = ? WHERE cat_id IN(%s)", implode(',', $in_prepare)));
      $update_stm->execute(array_merge(array($date), array_keys($in_prepare)));
    }
    
  }

  /**
   * Deletes a particular message from the specified catalogue.
   *
   * @param string $message   the source message to delete.
   * @param string $catalogue the catalogue to delete from.
   * @return boolean true if deleted, false otherwise. 
   */
  function delete($message, $catalogue = 'messages')
  {
    $cat_id = $this->getCatalogueId($catalogue);

    // the catalogue does not exist
    if($cat_id === false)
    {
      
      return false;
    }
    
    $stm = $this->pdo->prepare("DELETE FROM trans_unit WHERE cat_id = ? AND source = ?");

    return $stm->execute(array($cat_id, $message));
  }

  /**
   * Updates the translation.
   *
   * @param string $text      the source string.
   * @param string $target    the new translation string.
   * @param string $comments  comments
   * @param string $catalogue the catalogue of the translation.
   * @return boolean true if translation was updated, false otherwise. 
   */
  function update($text, $target, $comments, $catalogue = 'messages')
  {
    
    $cat_id = $this->getCatalogueId($catalogue);
    $date   = strtotime('now');
    
    $stm = $this->pdo->prepare("UPDATE trans_unit SET target = ?, date_modified = ? WHERE cat_id = ? AND source = ?");
    $stm->execute(array($target, $date, $cat_id, $text));
  }
  
  /**
   * Store a message into the database
   *
   * @param string $catalogue the catalogue name
   * @param string $source the souurce
   * @param string $target the target
   */
  public function insert($source, $target, $comments, $catalogue = 'messages')
  {
   
    $stm    = $this->pdo->prepare("INSERT INTO trans_unit (cat_id, source, target, date_added) VALUES (?, ?, ?, ?)");    
    $cat_id = $this->getCatalogueId($catalogue, true);
    
    return $stm->execute(array($cat_id, $source, $target, strtotime('now')));
  }
  
  public function getId()
  {
    return md5($this->source);
  }
}
