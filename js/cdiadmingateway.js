
// Guide Accordeon
jQuery(document).ready(function(){ 
  jQuery('.faqtitre').click(function(e){
    var content = jQuery(this).children( '.faqcontent' );
    if (jQuery(content).is(':visible') ){ 
      jQuery(content).hide() ;
    }else{
      jQuery(content).show() ;
    }
    e.stopPropagation();
  });
});
			
// CDI Debug		
jQuery(document).ready(function($){ 
  var cdidebugarea = $( '#cdi-debug-area' );
  //var ajaxurl = "<?php echo $ajaxurl; ?>";
  var doopen          = $( '#cdi-debug-open' );
  var doclose          = $( '#cdi-debug-close' );
  var docloseajax      = { 'action': 'cdi_debug_close_view' };
  var doclear          = $( '#cdi-debug-clear' );
  var doclearajax      = { 'action': 'cdi_debug_clear_file' };
  var dorefresh          = $( '#cdi-debug-refresh' );
  var doselect          = $( '#cdi-debug-select' );
  var currentselect ;
  jQuery(doopen).click(function(){
    currentselect = 'cdi' ;
    var doopenajax      = { 'action': 'cdi_debug_open_view', 'select': currentselect };
    jQuery.post(ajaxurl, doopenajax, function(response) {
	        cdidebugarea.html( response );
	        cdidebugarea.scrollTop( cdidebugarea[0].scrollHeight );
    } );
    jQuery( "#debugmanage" ).each(function( index ) { 
      jQuery(this).show();
	      });
    $(".mode-run").css("background-color", "red");
    jQuery('html, body').animate({ scrollTop: jQuery("#debugmanagewrap").offset().top  - 32}, 1000);
  } );
  jQuery(doclose).click(function(){
    jQuery.post(ajaxurl, docloseajax, function(response) {
    } );
    jQuery( "#debugmanage" ).each(function( index ) { 
      jQuery(this).hide();
	      });
    $(".mode-run").css("background-color", "#0085ba");
    jQuery('html, body').animate({ scrollTop: jQuery("#wpbody").position().top }, 1000);
  } );
  jQuery(doclear).click(function(){
    jQuery.post(ajaxurl, doclearajax, function(response) {
	        cdidebugarea.html( response );
	        cdidebugarea.scrollTop( cdidebugarea[0].scrollHeight );
    } );
  } );
  jQuery(dorefresh).click(function(){
    var dorefreshajax      = { 'action': 'cdi_debug_refresh_view', 'select': currentselect };
    jQuery.post(ajaxurl, dorefreshajax, function(response) {  
	        cdidebugarea.html( response );
	        cdidebugarea.scrollTop( cdidebugarea[0].scrollHeight );
    } );
  } );
  jQuery(doselect).change(function(){
    currentselect = $(this).val() ;
    var dorefreshajax      = { 'action': 'cdi_debug_refresh_view', 'select': currentselect };
    jQuery.post(ajaxurl, dorefreshajax, function(response) {
	        cdidebugarea.html( response );
	        cdidebugarea.scrollTop( cdidebugarea[0].scrollHeight );
    } );
  } );
});

// Gateway Bordereaux
jQuery(document).ready(function($){ 
  var cdibremiselisteselected = $( '#cdi-bremise-listeselected' );
  var cdilistbordereau = $( '#cdi-list-bordereau' );
  var cdibremisemessagezone = $( '#cdi-bremise-message-zone' );
  //var ajaxurl           = "<?php echo $ajaxurl; ?>";
  var doopen            = $( '#cdi-bremise-open' );
  var doclose           = $( '#cdi-bremise-close' );
  var doselect          = $( '#cdi-bremise-select' );
  var doclear           = $( '#cdi-bremise-clear' );
  var carrierexec       = $( '#cdi-bcarrier-exec' );
  var doexec           = $( '#cdi-bremise-exec' );
  var traexec           = $( '#cdi-btransport-exec' );
  var colexec           = $( '#cdi-bpreparation-exec' );
  var livexec           = $( '#cdi-blivraison-exec' );
  var bbulklabelpdfexec = $( '#cdi-bbulklabelpdf-exec' );   
  var bbulkcn23pdfexec  = $( '#cdi-bbulkcn23pdf-exec' );   
  var histocsvexec      = $( '#cdi-bhistoriquecsv-exec' );  
  function getcurrentselect() {
    var currentselect = {
        br_select_gateway:$(document.getElementById("br_select_gateway")).is(':checked'), 
        br_select_orders:$(document.getElementById("br_select_orders")).is(':checked'), 
            cdi_o_Br_select_fromdate:document.getElementById("cdi_o_Br_select_fromdate").value,
            cdi_o_Br_select_todate:document.getElementById("cdi_o_Br_select_todate").value, 
        br_select_numorders:$(document.getElementById("br_select_numorders")).is(':checked'), 
            br_select_numorders_param1:document.getElementById("br_select_numorders_param1").value,
        br_select_codesuivi:$(document.getElementById("br_select_codesuivi")).is(':checked'), 
            br_select_codesuivi_param1:document.getElementById("br_select_codesuivi_param1").value,
        br_select_extern:$(document.getElementById("br_select_extern")).is(':checked'), 
            br_select_extern_param1:document.getElementById("br_select_extern_param1").value,
        br_select_annulcode:$(document.getElementById("br_select_annulcode")).is(':checked'), 
            br_select_annulcode_param1:document.getElementById("br_select_annulcode_param1").value
         };
    return currentselect ;
  }
  function majselectedcarrier() {
    var carrier = this.value ;
    cdibremisemessagezone.html(""); 
    var carrierexecajax      = { 'action': 'cdi_bcarrier_exec_bordereau' , 'carrier' : this.value };
    jQuery.post(ajaxurl, carrierexecajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
	        cdilistbordereau.html(arr['htmlbordereaux']);
    } );
    if (this.value == "colissimo") { // Only Bordereau de remise for Colissimo
      jQuery( "#cdi-bremise-exec" ).each(function( index ) { 
        jQuery(this).show();
	        });
    }else{
      jQuery( "#cdi-bremise-exec" ).each(function( index ) { 
        jQuery(this).hide();
	        });
    }
  }    
  jQuery(doopen).click(function(){
    cdibremisemessagezone.html(""); 
    var doopenajax      = { 'action': 'cdi_bremise_open_view', 'select': getcurrentselect() };
    jQuery.post(ajaxurl, doopenajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
	        cdilistbordereau.html(arr['htmlbordereaux']);
    } );
    jQuery( "#bremisemanage" ).each(function( index ) { 
      jQuery(this).show();
	      });
    $(".mode-run-bremise").css("background-color", "red");
    jQuery('html, body').animate({ scrollTop: jQuery("#bremisemanagewrap").offset().top - 32} , 1000);
    jQuery(carrierexec).each(function(){    
      majselectedcarrier.call(this) ;    
    } );    
  } );
  jQuery(doclose).click(function(){
    cdibremisemessagezone.html(""); 
    var docloseajax      = { 'action': 'cdi_bremise_close_view' };
    jQuery.post(ajaxurl, docloseajax, function(response) {
    } );
    jQuery( "#bremisemanage" ).each(function( index ) { 
      jQuery(this).hide();
	      });
    document.getElementById("br_select_numorders_param1").value = '';
    document.getElementById("br_select_codesuivi_param1").value = '';
    document.getElementById("br_select_extern_param1").value = '';
    document.getElementById("br_select_annulcode_param1").value = '';
    document.getElementById("cdi_o_Br_select_fromdate").value = '';
    document.getElementById("cdi_o_Br_select_todate").value = '';
    $(".mode-run-bremise").css("background-color", "#0085ba");
    jQuery('html, body').animate({ scrollTop: jQuery("#wpbody").position().top }, 1000);
  } );
  jQuery(doselect).click(function(){
    cdibremisemessagezone.html(""); 
    var doselectajax      = { 'action': 'cdi_bremise_add_select', 'select': getcurrentselect() };
    jQuery.post(ajaxurl, doselectajax, function(response) {   
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
    } );
    document.getElementById("br_select_gateway").checked = false;
    document.getElementById("br_select_orders").checked = false;
    document.getElementById("br_select_numorders").checked = false;
    document.getElementById("br_select_codesuivi").checked = false;
    document.getElementById("br_select_extern").checked = false;
    document.getElementById("br_select_annulcode").checked = false;
  } );
  jQuery(doclear).click(function(){
    cdibremisemessagezone.html(""); 
    var doclearajax      = { 'action': 'cdi_bremise_clear_select'};
    jQuery.post(ajaxurl, doclearajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
    } );
    document.getElementById("br_select_gateway").checked = false;
    document.getElementById("br_select_orders").checked = false;
    document.getElementById("br_select_numorders").checked = false;
    document.getElementById("br_select_codesuivi").checked = false;
    document.getElementById("br_select_extern").checked = false;
    document.getElementById("br_select_annulcode").checked = false;
  } );
  jQuery(carrierexec).change(function(){
    majselectedcarrier.call(this) ; 
  } );           
  jQuery(doexec).click(function(){
    cdibremisemessagezone.html(""); 
    var doexecajax      = { 'action': 'cdi_bremise_exec_bordereau'};
    jQuery.post(ajaxurl, doexecajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
	        cdilistbordereau.html(arr['htmlbordereaux']);
    } );
  } );
  jQuery(traexec).click(function(){
    cdibremisemessagezone.html(""); 
    var doexecajax      = { 'action': 'cdi_btransport_exec_bordereau'};
    jQuery.post(ajaxurl, doexecajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
	        cdilistbordereau.html(arr['htmlbordereaux']);
    } );
  } );
  jQuery(colexec).click(function(){
    cdibremisemessagezone.html(""); 
    var doexecajax      = { 'action': 'cdi_bpreparation_exec_bordereau'};
    jQuery.post(ajaxurl, doexecajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
	        cdilistbordereau.html(arr['htmlbordereaux']);
    } );
  } );
  jQuery(livexec).click(function(){
    cdibremisemessagezone.html(""); 
    var doexecajax      = { 'action': 'cdi_blivraison_exec_bordereau'};
    jQuery.post(ajaxurl, doexecajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
	        cdilistbordereau.html(arr['htmlbordereaux']);
    } );
  } );
  jQuery(bbulklabelpdfexec).click(function(){
    cdibremisemessagezone.html(""); 
    var doexecajax      = { 'action': 'cdi_bbulklabelpdf_exec_bordereau'};
    jQuery.post(ajaxurl, doexecajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
	        cdilistbordereau.html(arr['htmlbordereaux']);
    } );
  } );   
  jQuery(bbulkcn23pdfexec).click(function(){
    cdibremisemessagezone.html(""); 
    var doexecajax      = { 'action': 'cdi_bbulkcn23pdf_exec_bordereau'};
    jQuery.post(ajaxurl, doexecajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
	        cdilistbordereau.html(arr['htmlbordereaux']);
    } );
  } );   
  jQuery(histocsvexec).click(function(){
    cdibremisemessagezone.html(""); 
    var doexecajax      = { 'action': 'cdi_bhistocsv_exec_bordereau'};
    jQuery.post(ajaxurl, doexecajax, function(response) {
      arr = JSON.parse(response) ;
	        cdibremisemessagezone.html(arr['message']);
	        cdibremiselisteselected.html(arr['htmlselected']);
	        cdilistbordereau.html(arr['htmlbordereaux']);
    } );
  } );           
  
});
jQuery(document).ready(function($) {
  $('.custom_date').datepicker({
    dateFormat : 'yy-mm-dd'
  });
});

