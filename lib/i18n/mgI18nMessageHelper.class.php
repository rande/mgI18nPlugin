<?php
/*
 * This file is part of the mgWidgetsPlugin package.
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
class mgI18nMessageHelper
{

  public static function appendMessages($from, $append)
  {

    foreach($append as $catalogue => $messages)
    {

      foreach($messages as $message)
      {
        
        $from[] = array(
          'message'   => $message[0],
          'params'    => (count($message) == 2) ? $message[1] : array(),
          'catalogue' => $catalogue
        );
      }
    }

    return $from;
  }

}
