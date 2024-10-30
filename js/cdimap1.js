jQuery(document).ready(function(relocatediv) {
  
  if (jQuery('.woocommerce-checkout').length) { // Exist if Classic Checkout
    var target = document.querySelector('.woocommerce-checkout');
    var checkoutdiv = '.woocommerce-checkout' ;
    var wherecheckoutdefaut = '#order_review' ;
    var wherecheckoutshoptable = '.shop_table' ;
    var wherecheckoutpayment = '#payment' ;
  } 
  if (jQuery('.wp-block-woocommerce-checkout').length) { // Exist if Blocks Checkout
    var target = document.querySelector('.wp-block-woocommerce-checkout')
    var checkoutdiv = '.wp-block-woocommerce-checkout' ;
    var wherecheckoutdefaut = '#shipping-option' ;
    var wherecheckoutshoptable = '.wp-block-woocommerce-checkout-order-summary-block' ;
    var wherecheckoutpayment = '#payment-method' ;
  }

  var cdiobserver = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'childList' || mutation.type === 'subtree') {
        if (mutation.addedNodes) {
          if (mutation.addedNodes[0]) {
            if (mutation.addedNodes[0].className) {
              if (mutation.addedNodes[0].className === 'cdiselectlocation') {
                var $node = jQuery(mutation.addedNodes[0]);
                var idnewselect = mutation.addedNodes[0].id;
                // Relocate Div
                var where = cdiwhereselectorpickup;
                switch (where) {
                  case 'insertBefore( ".shop_table" )':
                    jQuery($node).insertBefore(wherecheckoutshoptable);
                    break;
                  case 'insertAfter( ".shop_table" )':
                    jQuery($node).insertAfter(wherecheckoutshoptable);
                    break;
                  case 'insertBefore( "#payment" )':
                    jQuery($node).insertBefore(wherecheckoutpayment);
                    break;
                  case 'insertAfter( "#payment" )':
                    jQuery($node).insertAfter(wherecheckoutpayment);
                    break;
                  case 'insertBefore( "#order_review" )':
                    jQuery($node).insertBefore(wherecheckoutdefaut);
                    break;
                  case 'insertAfter( "#order_review" )':
                    jQuery($node).insertAfter(wherecheckoutdefaut);
                    break;
                  default:
                    eval ('jQuery($node).' + where) ;
                }               
                // Suppress old Div  
                jQuery(".cdiselectlocation").each(function(index) {
                  var currentID = this.id;
                  if (currentID != idnewselect) {
                    jQuery(this).remove();
                  }
                });              
                if (jQuery('#cdimapcontainer').length){
                    jQuery('html, body').animate({ scrollTop: jQuery("#cdimapcontainer").offset().top  - 32}, 1500);
                }   
                // Restart after events
                cdiobserver.disconnect();   
                setTimeout(function(){ startobserver() }, 300);          
              }
            }
          }
        }
      }
    });
  });
  if (target !== null) {
    startobserver() ;
  }
  function startobserver(){ 
    var config = {
      childList: true,
      subtree: true
      };
    cdiobserver.observe(target, config);
  }

    // Here beginning of special js code if Woocommerce Blocks Checkout
    // Everything that follows is to overcome WC blocks bugs

    // To replace filter 'woocommerce_cart_shipping_method_full_label' not called
    setTimeout(function(){
        if (jQuery('.wp-block-woocommerce-checkout').length) { // Exist if Blocks Checkout 
            cdistartshippingmethodfulllabel() ;
            jQuery('#shipping-option').change(function(){
                cdistartshippingmethodfulllabel() ;
            });  
        }
    }, 300);
    function cdistartshippingmethodfulllabel(){ 
            jQuery("input").each(function(){
                shippingmethod = this.value ;  
                arrshippingmethod = shippingmethod.split('_');
                if (arrshippingmethod[0] == 'cdi' && arrshippingmethod[1] == 'shipping') {
                    var spanlabel = jQuery('span[id="radio-control-0-'+shippingmethod+'__label"]').html() ;
                    if ( spanlabel.indexOf('</span></span>') < 0 ) { // To avoid embeded changes of label     
                        var origlabel = jQuery('span[id="radio-control-0-'+shippingmethod+'__label"]').text() ;                
                        var data = { 'action': 'woocommerce_cart_shipping_method_full_label', 'shippingmethod': shippingmethod, 'origlabel': origlabel };
                        var ajaxurl = cdiajaxurl ; 
                        jQuery.post(ajaxurl, data, function(response) {
                            if (response[0].length) {
                                var arrresponse = jQuery.parseJSON(response); 
                                var shippingmethod = arrresponse['shippingmethod'] ;
                                var changlabel = arrresponse['changlabel'] ;
                                var origlabel = arrresponse['origlabel'] ;              
                                if (origlabel != changlabel) {
                                    var spanorig = jQuery('span[id="radio-control-0-'+shippingmethod+'__label"]') ;
                                    var spanresult = '<span id="radio-control-0-'+shippingmethod+'__label" class="wc-block-components-radio-control__label">'+changlabel+'</span>' ;
                                    jQuery('span[id="radio-control-0-'+shippingmethod+'__label"]').html(spanresult) ;                                                                           
                                }
                            }                 
                        });
                    }
                 }
            }); 
    } 

    // To replace action 'cdi_woocommerce_review_order_after_cart_contents' not called
    setTimeout(function(){
        if (jQuery('.wp-block-woocommerce-checkout').length) { // Exist if Blocks Checkout 
            cdirevieworderaftercart_contents()
            jQuery('#shipping-option').change(function(){
                cdirevieworderaftercart_contents()
            });  
            jQuery('#shipping-fields').change(function(){
                cdirevieworderaftercart_contents()
            });        
        }
    }, 300);
    function cdirevieworderaftercart_contents(){ 
        setTimeout(function(){             
            var data = { 'action': 'woocommerce_review_order_after_cart_contents' };
            var ajaxurl = cdiajaxurl ; 
            jQuery.post(ajaxurl, data, function(response) {
                var html = jQuery.parseJSON(response);
                jQuery('.wp-block-woocommerce-checkout').append(html) ;
            });
        }, 2000);
    } 

});
