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
class mgI18nPluginConfiguration extends sfPluginConfiguration
{

  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'defineConfiguration'));
  }

  public function defineConfiguration(sfEvent $event)
  {
    $context = $event->getSubject();
    
    if ($context->getI18N())
    {
      $i18n_options = $cokontext->getI18N()->getOptions();
    }
    else
    {
      throw new sfException('Please read the README file to install the plugin.');
    }
    
    sfConfig::set('mg_i18n_enabled', mgI18nUser::canTranslate($context));
    sfConfig::set('mg_i18n_global_application', isset($i18n_options['global_application']) ? $i18n_options['global_application'] : $context->getConfiguration()->getApplication());
  }
}