<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCommonFilter automatically adds the translated messages as a json
 *
 * @package    mgI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class mgI18nFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    if(!sfConfig::get('mg_i18n_enabled', false))
    {
      
      return;
    }
    // execute this filter only once
    $response = $this->context->getResponse();

    // include javascripts and stylesheets
    $content = $response->getContent();
    
    if (false !== ($pos = strpos($content, '</head>')))
    {

      $html = '';
      $html .= "<script>\n";
      $html .= "\tvar _mg_i18n_messages = ".json_encode($this->context->getI18n()->getRequestedMessages());
      $html .= "\n</script>\n";

      $response->setContent(substr($content, 0, $pos).$html.substr($content, $pos));
    }
  }
}
