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
class sfMessageSource_mgMySQL extends sfMessageSource_MySQL
{
  protected $application = null;

  protected $informations;

  /**
   * The original sfMessageSource_MySQL runs too many query on the database server
   * So I have added a new loadInformations methods to preload information
   *
   * @param unknown_type $source
   */
  function __construct($source)
  {
    $this->source = (string) $source;
    $this->dsn = $this->parseDSN($this->source);
    $this->db = $this->connect();
    $this->loadInformations();
  }

  public function load($catalogue = null)
  {
    if($catalogue == null || strlen($catalogue) == 0)
    {
      $catalogue = 'messages';
    }

    $catalogue = sfContext::getInstance()->getConfiguration()->getApplication().'.'.$catalogue;

    return parent::load($catalogue);
  }

  public function connect()
  {
    static $conn;

    if(!$conn)
    {
      $conn = parent::connect();

      mysql_query("SET CHARACTER SET utf8", $conn);
    }

    return $conn;
  }

  /**
   * preload catalogue information
   *
   */
  public function loadInformations()
  {

    $rs = mysql_query("SELECT cat_id, name, date_modified FROM catalogue", $this->db);

    $this->informations = array();
    while($row = mysql_fetch_array($rs, MYSQL_NUM))
    {

      $this->informations[$row[1]] = array('cat_id' => $row[0], 'date_modified' => $row[2]);
    }
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
    $rs = mysql_query("SELECT COUNT(*) FROM trans_unit WHERE cat_id = {$cat_id}", $this->db);

    $count = intval(mysql_result($rs, 0));

    return array($cat_id, $variant, $count);
  }

  /**
   * @see sfMessageSource_MySQL
   *
   */
  protected function updateCatalogueTime($cat_id, $variant)
  {
    $time = time();

    $result = mysql_query("UPDATE catalogue SET date_modified = {$time} WHERE cat_id = {$cat_id}", $this->db);

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
}
