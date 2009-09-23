/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    mgI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
function mgI18nPlugin(options)
{

  this.url_translation = null;
  this.url_messages    = null;

  this.hide_translated = false;


  this.page = {
    loaded: true,
    messages: {},
    panel: null
  }

  this.application = {
    loaded: false,
    messages: {},
    panel: null
  }

  this.lib = {
    loaded: false,
    messages: {},
    panel: null
  }

  this.ajax = {
    loaded: false,
    messages: {},
    panel: null
  }

  this.init(options);
}

mgI18nPlugin.state    = {
  mode: 'none',
  dragged: false
}

mgI18nPlugin.instance = null;

mgI18nPlugin.prototype.displayLoading = function(mode)
{
  if(mode == 'show')
  {
    jQuery('#mg-i18n-left-box').hide();
    jQuery('#mg-i18n-right-box').hide();
    jQuery('#mg-i18n-loading-box').show();

  }
  else
  {
    jQuery('#mg-i18n-left-box').show();
    jQuery('#mg-i18n-right-box').show();
    jQuery('#mg-i18n-loading-box').hide();
  }
}

mgI18nPlugin.prototype.init = function(options)
{

  this.url_translation = options.url_translation || null;
  this.url_messages = options.url_messages || null;

  // create the dialog box
  jQuery('#mg-i18n-dialog').draggable({
    appendTo: 'body',
    zIndex: 10000,
    handle: 'h2',
    start: function(event, ui) {

    },
    stop: function(event, ui) {
      mgI18nPlugin.state.dragged = true;

      jQuery(this).css('zIndex', 10000);
    }
	});

  jQuery('#mg-i18n-on-top-box').click(function(){

    if(mgI18nPlugin.state.dragged)
    {
      mgI18nPlugin.state.dragged = false;
      return;
    }

    mgI18nPlugin.instance.toggleModalState(
      jQuery('#mg-i18n-left-box').css('display') == 'none' ? 'show' : 'hide'
    );
  });

  // create the tabulation
  jQuery('#mg-i18n-left-box').tabs({
    select: function(event, ui) {

      var rel = ui.panel.getAttribute('rel');

      if(!rel)
      {

        return;
      }

      if(mgI18nPlugin.instance[rel].loaded == true)
      {

        return;
      }

      jQuery.ajax({
        type: 'GET',
        url: mgI18nPlugin.instance.url_messages.replace('MESSAGE_TYPE', rel),
        data: {},
        dataType: "json",
        cache: false,
        success: function(data, textStatus) {
          var type = data.type;
          var messages = data.messages;

          mgI18nPlugin.instance.loadTranslationTable(type, messages);
        }
      });

      mgI18nPlugin.instance.displayLoading('show');

    }
  });

  // handle the translation form
  jQuery('#mg-i18n-form-update').submit(function(event) {

    event.preventDefault();

    jQuery('#mg-i18n-loading').show();
    jQuery('#mg-i18n-submit').hide();

    jQuery.ajax({
      type: 'GET',
      url: jQuery('#mg-i18n-form-update').attr('action'),
      data: jQuery("#mg-i18n-form-update").serialize(),
      cache: false,
      success: function(data, textStatus) {
        jQuery('#mg-i18n-loading').hide();
        jQuery('#mg-i18n-submit').show();
      }
    })

    return false;
  });

  // handle hide translation checkbox
  jQuery('input.mg-i18n-hide-translated').change(function() {

    var panel = jQuery(this).parent().parent();
    var display = jQuery(this).attr('checked');

    mgI18nPlugin.instance.displayTranslated(panel, display);
  });

  jQuery('input.mg-i18n-current-page-search').keyup(function() {

    var panel = jQuery(this).parent().parent();
    var value = jQuery(this).val();

    mgI18nPlugin.instance.filterTranslated(panel, value);
  });

  this.page.panel        = jQuery('div#mg-i18n-panel-page');
  this.application.panel = jQuery('div#mg-i18n-panel-application');
  this.lib.panel         = jQuery('div#mg-i18n-panel-lib');
  this.ajax.panel        = jQuery('div#mg-i18n-panel-ajax');

  jQuery('.mg-i18n-parameters').hide();

  // load the translation from the current page
  this.loadTranslationTable('page', _mg_i18n_messages);

  this.toggleModalState('hide');
}

mgI18nPlugin.prototype.toggleModalState = function(mode)
{

  jQuery('#mg-i18n-dialog').show();

  if(mode == 'show')
  {

    jQuery('#mg-i18n-dialog')
      .fadeTo(0, 1)
      .animate({width: '650px', heigth: '400px'}, 500)
      .css('zIndex', 10000)
    ;

    jQuery('#mg-i18n-left-box').show();
    jQuery('#mg-i18n-right-box').show();

  }
  else
  {
    jQuery('#mg-i18n-dialog').css('height', null);
    jQuery('#mg-i18n-dialog').css('width', null);
    jQuery('#mg-i18n-dialog').fadeTo(0, 0.25)

    jQuery('#mg-i18n-left-box').hide();
    jQuery('#mg-i18n-right-box').hide();
  }

}

mgI18nPlugin.prototype.displayTranslated = function(panel, display)
{
  this.hide_translated = display;

  if(this.hide_translated === true)
  {
    jQuery('tr.mg-target-translated', panel).hide();
  }
  else
  {
    jQuery('tr', panel).show()
  }

  this.filterTranslated(panel, jQuery('input.mg-i18n-current-page-search', panel).val());
}

mgI18nPlugin.prototype.filterTranslated = function(panel, value)
{

  jQuery('tr', panel).hide();

  var re = new RegExp(value, 'ig');

  jQuery('tr', panel).each(function() {
    var match = false;
    jQuery('td', this).each(function() {
      if(jQuery(this).html().match(re))
      {
        match = true;
      }

      return;
    })

    if(match)
    {
      if(mgI18nPlugin.instance.hide_translated && jQuery(this).hasClass('mg-target-translated'))
      {
        return;
      }

      jQuery(this).show();
    }
  });
}

mgI18nPlugin.prototype.loadTranslationTable = function(name, mg_i18n_messages)
{

  this[name].messages = mg_i18n_messages;
  this[name].loaded   = true;

  var tbody = jQuery('tbody', this[name].panel);

  var html = "";

  for(name_catalogue in this[name].messages)
  {
    var catalogue = this[name].messages[name_catalogue];
    var display_catalogue = name_catalogue.split(".")[1];

    for(index in catalogue)
    {
      trans = catalogue[index];

      html += "<tr catalogue='" + name_catalogue + "' source='" + trans.source + "' rel='" + name + "' class='_mg_i18_td_unselected " + (trans.is_translated ? 'mg-target-translated' : 'mg-target-non-translated') + "'><td hash='" + index + "'>" + display_catalogue + "</td><td>" + trans.target + "</td></tr>";

    }
  }

  tbody.append(html);

  jQuery('tr', tbody)
    .mouseover(function() {
      jQuery(this).css('cursor', 'pointer')
    })
    .click(function() {
      var tr = jQuery(this);
      var panel =  mgI18nPlugin.instance[tr.attr('rel')];
      var tds = jQuery('td', this);

      jQuery('td', tbody)
        .removeClass('_mg_i18_td_selected')
        .addClass('_mg_i18_td_unselected');

      tds
        .removeClass('_mg_i18_td_unselected')
        .addClass('_mg_i18_td_selected');

      // toggle the loading icon
      jQuery('#mg-i18n-loading').show();
      jQuery('#mg-i18n-submit').hide();

      // clear the form
      jQuery('input[type=text]', '#mg-i18n-form-update').val('');
      jQuery('textarea', '#mg-i18n-form-update').val('');

      var catalogue = tr.attr('catalogue');
      var source    = tr.attr('source');
      var hash      = jQuery(tds.get(0)).attr('hash');
      var i18n_params     = panel.messages[catalogue][hash]['params'];

      // set variables and submit form to get the variable
      jQuery('#mg-i18n-catalogue').val(catalogue);
      jQuery('#mg-i18n-source').val(source);

      if(i18n_params && i18n_params.length > 0)
      {
        jQuery('.mg-i18n-parameters').show();
        jQuery('#mg-i18n-parameters-text').html(i18n_params);
      }
      else
      {
        jQuery('.mg-i18n-parameters').hide();
      }

      jQuery.ajax({
        type: 'GET',
        url: mgI18nPlugin.instance.url_translation,
        dataType: "json",
        data: jQuery("#mg-i18n-form-update").serialize(),
        cache: false,
        success: function(data, textStatus) {
          for(var param in data) {
            jQuery('#' + param, "#mg-i18n-form-update").val(data[param]);
          }

          jQuery('#mg-i18n-loading').hide();
          jQuery('#mg-i18n-submit').show();
        }
      })
    });

  this.displayLoading('hide');
}