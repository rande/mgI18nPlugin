/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2008 MenuGourmet 
 *
 * Author : Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if(typeof jQuery != 'undefined') {
  jQuery(window).bind('load', function() {
  
    jQuery('#_mg_i18n_loading').hide();
    jQuery('#_mg_i18n_submit').hide();
    jQuery('._mg_i18n_parameters').hide();
    
    var tbody = jQuery('#_mg_i18n_tbody');
  
    for(name_catalogue in _mg_i18n_messages)
    {
      var catalogue = _mg_i18n_messages[name_catalogue];

      for(index in catalogue)
      {
        trans = catalogue[index];
      
        tbody.append("<tr><td hash='" + index + "'>" + name_catalogue + "</td><td>" + trans.source + "</td><td>" + trans.target+ "</td></tr>");
      
        jQuery('tr:last', tbody).click(function() {
          var tds = jQuery('td', this);
        
          jQuery('td', tbody)
            .removeClass('_mg_i18_td_selected')
            .addClass('_mg_i18_td_unselected');
        
          tds
            .removeClass('_mg_i18_td_unselected')
            .addClass('_mg_i18_td_selected');
        
          // toggle the loading icon
          jQuery('#_mg_i18n_loading').show();
          jQuery('#_mg_i18n_submit').hide();
        
          // clear the form
          jQuery('input[type=text]', '#_mg_i18n_form').val('');
          jQuery('textarea', '#_mg_i18n_form').val('');
        
          var catalogue = jQuery(tds.get(0)).html();
          var source    = jQuery(tds.get(1)).html();
          var hash      = jQuery(tds.get(0)).attr('hash');
          var i18n_params     = _mg_i18n_messages[catalogue][hash]['params'];
          
          // set variables and submit form to get the variable
          jQuery('#_mg_i18n_catalogue').val(catalogue);
          jQuery('#_mg_i18n_source').val(source);
        
          if(i18n_params && i18n_params.length > 0)
          {
            jQuery('._mg_i18n_parameters').show();
            jQuery('#_mg_i18n_parameters_text').html(i18n_params);
          }
          else
          {
            jQuery('._mg_i18n_parameters').hide();
          }
          
          jQuery.ajax({
            type: 'GET',
            url: '/backend.php/mgI18nAdmin/getTargets',
            dataType: "json",
            data: jQuery("#_mg_i18n_form").serialize(),
            cache: false,
            success: function(data, textStatus) {
              for(var param in data) {
                jQuery('#' + param, "#_mg_i18n_form").val(data[param]);
              }
            
              jQuery('#_mg_i18n_loading').hide();
              jQuery('#_mg_i18n_submit').show();
            }
          })
        });
      
        jQuery('td', tbody)
          .addClass('_mg_i18_td_unselected')
          .mouseover(function() {
            jQuery(this).css('cursor', 'pointer')
          })
      }
    }
  
    jQuery('#_mg_i18n_form').submit(function() {
 
      jQuery('#_mg_i18n_loading').show();
      jQuery('#_mg_i18n_submit').hide();
  
      jQuery.ajax({
        type: 'GET',
        url: '/backend.php/mgI18nAdmin/updateTargets',
        data: jQuery("#_mg_i18n_form").serialize(),
        cache: false,
        success: function(data, textStatus) {
          jQuery('#_mg_i18n_loading').hide();
          jQuery('#_mg_i18n_submit').show();
        }
      })
    
      return false;
    });
  });
}