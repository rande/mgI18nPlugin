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
    $this->dispatcher->connect('context.load_factories', array($this, 'defineUserPermission'));
  }

  public function defineUserPermission(sfEvent $event)
  {

    $context = $event->getSubject();

    sfConfig::set('mg_i18n_enabled', mgI18nUser::canTranslate($context));
  }
}