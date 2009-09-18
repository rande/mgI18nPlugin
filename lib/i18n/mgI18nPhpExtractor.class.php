<?php

/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * Original works from Fabien Potencier
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    ngI18nPlugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Thomas Rabaix <thomas.rabaix@gmail.com>
 *
 * @version    SVN: $Id$
 */
class mgI18nPhpExtractor implements sfI18nExtractorInterface
{
  const
    NONE          = 0,
    OPEN          = 1,
    IN_MESSAGE    = 2,
    IN_PARAMETERS = 3,
    IN_CATALOGUE  = 4;

  /**
   * Extract i18n strings for the given content.
   *
   * @param  string $content The content
   *
   * @return array An array of i18n strings
   */
  public function extract($content)
  {
    $tokens = token_get_all($content);

    $strings = array();
    $pos     = -1;
    $deep    = 0;
    
    $i18n_function = self::NONE;
    $line = 0;
    $heredoc = false;
    $buffer = '';
    foreach ($tokens as $token)
    {
      if (is_string($token))
      {
        switch ($token)
        {

          case '(':
            if (self::OPEN == $i18n_function)
            {
              $i18n_function = self::IN_MESSAGE;
            } 
            else if(self::IN_PARAMETERS == $i18n_function)
            {
              $deep++;
            }

            break;
          case ',':

            if(self::IN_MESSAGE == $i18n_function)
            {
              $i18n_function = self::IN_PARAMETERS;
              $deep = 0;
            }
            else if(self::IN_PARAMETERS == $i18n_function && $deep <= 0)
            {
              $i18n_function = self::IN_CATALOGUE;
            }
            
            break;

          case ')':
            
            if(self::IN_PARAMETERS == $i18n_function)
            {
              $deep--;

              // no catalogue provided as a third arguments
              if($deep < 0)
              {
                $i18n_function = self::NONE;
              }
            } 
            else if(self::IN_MESSAGE == $i18n_function)
            {
              $i18n_function = self::NONE;
            }
            else if(self::IN_CATALOGUE == $i18n_function)
            {
              $i18n_function = self::NONE;
            }
            break;
        }
      }
      else
      {

        list($id, $text) = $token;

        switch ($id)
        {
          case T_STRING:
            if ($heredoc && self::IN_MESSAGE == $i18n_function)
            {
              $buffer .= $text;
            }
            else if($i18n_function == self::NONE)
            {
              $i18n_function = ('__' == $text || 'format_number_choice' == $text) ? self::OPEN : self::NONE;
              $pos++;
            }
            break;
          case T_WHITESPACE:
            break;
          case T_START_HEREDOC:
            $heredoc = true;
            break;
          case T_END_HEREDOC:
            $heredoc = false;
            if ($buffer)
            {
              $strings[$pos] = array(
                'message' => substr($buffer, 0, -1),
                'params'  => array(),
                'catalogue' => null
              );
            }

            break;
          case T_CONSTANT_ENCAPSED_STRING:
            if (self::IN_MESSAGE == $i18n_function)
            {
              $strings[$pos] = array(
                'message' => $this->extractString($text),
                'params'  => array(),
                'catalogue' => null
              );
            }
            else if(self::IN_CATALOGUE == $i18n_function)
            {
              $strings[$pos]['catalogue'] = $this->extractString($text);
            }
            break;
          default:
            if ($heredoc && self::IN_MESSAGE == $i18n_function)
            {
              $buffer .= $text;
            }
        }
      }
    }

    return $strings;
  }


  public function extractString($text)
  {
    $delimiter = $text[0];
    
    return str_replace('\\'.$delimiter, $delimiter, substr($text, 1, -1));
  }
}
