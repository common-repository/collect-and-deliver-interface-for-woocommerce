          jQuery(document).ready(function(){
            if ((linemaxsize !== 'no') && jQuery.isNumeric(linemaxsize)) {              
              jQuery("#billing_address_1").attr('maxlength',linemaxsize);
              jQuery("#billing_address_2").attr('maxlength',linemaxsize);
              jQuery("#billing_address_3").attr('maxlength',linemaxsize);
              jQuery("#billing_address_4").attr('maxlength',linemaxsize);
              jQuery("#shipping_address_1").attr('maxlength',linemaxsize);
              jQuery("#shipping_address_2").attr('maxlength',linemaxsize);
              jQuery("#shipping_address_3").attr('maxlength',linemaxsize);
              jQuery("#shipping_address_4").attr('maxlength',linemaxsize);
            }
          });
