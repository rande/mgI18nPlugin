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
class PluginmgI18nTargetsForm extends sfForm
{

  public function configure()
  {
    
    if(!$this->getOption('message_source') instanceof sfMessageSource)
    {
      
      throw new sfException('You must provide a valide message_source option');
    }

    $this->setWidgets(array(
      'catalogue' => new sfWidgetFormInput,
      'source'    => new sfWidgetFormInput,
      'targets'   => new sfWidgetFormChoiceMany(array(
        'choices' => sfConfig::get('app_mgI18nPlugin_cultures_available')
    ))
    ));

    $this->setValidators(array(
      'catalogue' => new sfValidatorString(array('required' => true)),
      'source'    => new sfValidatorString(array('required' => true)),
      'targets'   => new sfValidatorPass()
    ));
    
  }

  // we don't use this feature for now, the action should not be called in production
  public function addCSRFProtection($CSRFSecret)
  {
    
  }
  
  public function save()
  {
    $cultures = sfConfig::get('app_mgI18nPlugin_cultures_available');
    $source   = $this->getValue('source');
    $targets  = $this->getValue('targets');
  
    // build the catalogue array
    $markers = array();
    foreach( $cultures as $code => $name)
    {
      $catalogues[] = $this->getValue('catalogue').'.'.$code;
      $markers[]    = '?';
    }
    
    // get current translation for the current source
    $sql = sprintf("
      SELECT tu.msg_id, tu.cat_id, tu.target, tc.name as tc_name
      FROM trans_unit tu 
      LEFT JOIN catalogue as tc ON tc.cat_id = tu.cat_id 
      WHERE tc.name IN (%s) AND tu.source = ?", implode(', ', $markers));
    
    $pdo = $this->getOption('message_source')->getConnection();
    $stm = $pdo->prepare($sql);
    $stm->execute(array_merge($catalogues, array($source)));
    
    // initialize the update query statement
    $update_stm = $pdo->prepare("UPDATE trans_unit SET target = ? WHERE msg_id = ?");
    
    // update translation
    foreach($stm->fetchAll(PDO::FETCH_ASSOC) as $trans_unit)
    {
      $name_catalogue = $trans_unit['tc_name'];
      $culture = mgI18n::getLanguage($name_catalogue);
      
      $target = $targets[$culture];
      
      $update_stm->execute(array($target, $trans_unit['msg_id']));
      
      unset($cultures[$culture]);
    }

    foreach($cultures as $code => $name)
    {
       $this->getOption('message_source')->insert(
        $source, 
        $targets[$code],
        '',
        $this->getValue('catalogue').'.'.$code
      );
    }
    
    return true;
  }
}

