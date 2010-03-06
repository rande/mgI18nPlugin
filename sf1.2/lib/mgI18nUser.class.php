<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Copy-and-paste this class into the project|application lib folder and
 * customize the logic depends on the specification.
 *
 * @package    mgI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class mgI18nUser
{

  /**
   * return true or false if the current user can use the translation tools
   * 
   * @param sfContext $context
   * @return boolean
   */
  public static function canTranslate(sfContext $context)
  {
    $sf_user = $context->getUser();

    if($sf_user instanceof sfGuardSecurityUser)
    {

      return $sf_user->isSuperAdmin();
    }

    return false;
  }
}
