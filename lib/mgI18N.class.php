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
 * 
 * @package    mgI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class mgI18N extends sfI18N
{

  protected 
    $requested_messages = array(),
    $to_analyse = array();


  public function initialize(sfApplicationConfiguration $configuration, sfCache $cache = null, $options = array())
  {
    parent::initialize($configuration, $cache, $options);

    $this->configuration->loadHelpers(array('Text'));
  }
  /**
   * Gets the translation for the given string
   *
   * @param  string $string     The string to translate
   * @param  array  $args       An array of arguments for the translation
   * @param  string $catalogue  The catalogue name
   *
   * @return string The translated string
   */
  public function __($string, $args = array(), $catalogue = 'messages')
  {
    $message = $this->getMessageFormat()->format($string, $args, $catalogue);
    
    $application = $this->getApplicationName();
    
    $catalogue = $application.'.'.$catalogue;

    if($this->options['debug'])
    {
      if(!array_key_exists($catalogue, $this->to_analyse))
      {
        $this->to_analyse[$catalogue] = array();
      }

      $transformed_string = $this->options['untranslated_prefix'].$string.$this->options['untranslated_suffix'];

      if($message == $transformed_string)
      {
        $this->to_analyse[$catalogue][] = $string;
      }
    }
    
    $params = '';
    
    if (is_array($args))
    {
      $params = implode(', ', array_keys($args));
    }

    if(sfConfig::get('mg_i18n_enabled'))
    {
      
      if(!array_key_exists($catalogue, $this->requested_messages))
      {
        $this->requested_messages[$catalogue] = array();
      }
      
      $value = array(
        'source' => $string,
        'target' => htmlentities(truncate_text($message, 70)),
        'params' => $params,
        'is_translated' =>  $string != $message
      );
      
      $this->requested_messages[$catalogue][md5($string)] = $value;
    }

    return $message;
  }

  public function getRequestedMessages()
  {
    
    return $this->requested_messages;
  }
  
  public function __destruct()
  {
    
    if(!$this->options['debug'])
    {
      // save only one debug mode
      return;
    }
    
    Doctrine::getTable('mgI18nCatalogue')->saveMessages(
      $this->to_analyse, 
      $this->getCulture()
    );
  }

  public function listenToChangeCultureEvent(sfEvent $event)
  {
    parent::listenToChangeCultureEvent($event);
    $this->to_analyse = array();
  }

  public function listenToChangeActionEvent(sfEvent $event)
  {
    // change message source directory to our module
    parent::listenToChangeActionEvent($event);
    $this->to_analyse = array();
  }

  public function getApplicationName()
  {

    return sfConfig::get('mg_i18n_global_application', $this->configuration->getApplication());
  }
}