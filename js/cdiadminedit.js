			
// Orderlist swith Ajax
jQuery(document).ready(function($) {
  $('.preview-cdi').click(function(){
    var mode = 'gateway' ; 
    if($(this).hasClass( "carrierredirected" )) {
      mode = 'carrierredirected' ;
    }
    if($(this).hasClass( "returnexecuted" )) {
      mode = 'return' ;
    }
    if($(this).hasClass( "synchrogateway" )) {
      mode = 'synchrogateway' ;
    }
    if($(this).hasClass( "waitingmetabox" )) {
      mode = 'waitingmetabox' ;
    }
    if($(this).hasClass( "orderlistlabel" )) {
      mode = 'orderlistlabel' ;
    }
    if($(this).hasClass( "orderlistcn23" )) {
      mode = 'orderlistcn23' ;
    }
    if($(this).hasClass( "resetmetabox" )) {
      mode = 'resetmetabox' ;
    }
    var data = {'action': 'cdi_orderlist_button', 'orderid': $(this).attr('id'), 'mode': mode};
    jQuery.post(ajaxurl, data, function(response) {
      var adresseActuelle = window.location;
      window.location = adresseActuelle;
    });
  });
});

// Orderlist Bulkactions
jQuery(document).ready(function() {
  jQuery('<option>').val('cdi_action_wcorderlist').text(cdiadminedittransinsert).appendTo("select[name='action']");
  jQuery('<option>').val('cdi_action_wcorderlist').text(cdiadminedittransinsert).appendTo("select[name='action2']");
});

