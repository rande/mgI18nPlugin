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

/**
 * Note : in futur make the symfony default extractor works with catalogue
 *        so replace this buggy code by the extractor
 *
 * @package    mgI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class mgI18nFileParser
{

  public function parseFiles(array $files)
  {
    $all_results = array();

    foreach($files as $file)
    {
      $lines = file($file);

      $phrases = array();

      $ereg = "/(.*)__\(([^\)]*)\)(.*)/";
      foreach($lines as $line)
      {
        if(preg_match($ereg, $line, $results))
        {
          $params = explode(',', $results[2]);

          // $phrase = ''tototo'' or '"tototo"'

          if(count($params) < 3)
          {
            // something is wrong
            $error     = true;
            $phrase    = false;
            $catalogue = false;
          }
          else
          {
            $phrase    = substr(trim($params[0]), 1, -1);
            $catalogue = substr(trim($params[2]), 1, -1);
            $error     = false;
          }



          $phrases[] = array(
            'phrase'    => $phrase,
            'catalogue' => $catalogue,
            'line'      => $line,
            'error'     => $error,
          );
        }

      }

      if(count($phrases) == 0)
      {
        continue;
      }

      $all_results[$file] = $phrases;
    }

    return $all_results;
  }
}