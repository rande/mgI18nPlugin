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

class mgI18N extends sfI18N
{

  protected 
    $requested_messages = array(),
    $to_analyse = array();

  
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
    
    $application = $this->configuration->getApplication();
    
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
    
    if(sfConfig::get('sf_web_debug'))
    {
      
      if(!array_key_exists($catalogue, $this->requested_messages))
      {
        $this->requested_messages[$catalogue] = array();
      }
      
      $value = array('source' => $string, 'target' => $message, 'params' => $params);
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

}