
// Shipping Carrier sélection
jQuery(document).ready(function(){ 
  //var cdimethod = '<?php echo $cdimethod; ?>'; 
  var cdimethod = 'colissimo_shippingzone_method';
  jQuery('#woocommerce_'+cdimethod+'_carrier').change(function(e){ 
    var carrier = this.value ;
    var table = document.getElementsByClassName("table_rate");  
    if (table.length == 0 ) {
      var carriers =  { "colissimo": "La Poste Colissimo", "mondialrelay": "Mondial Relay", "ups": "UPS", "collect": "Collect", "deliver": "Deliver", "notcdi": cdishipnotcdiname };     
      jQuery( "#cdimsgfurtif" ).remove();
      jQuery("<p id='cdimsgfurtif'>Vous devez maintenant enregistrer cette page (Bouton <strong>Enregistrez les Modifications</strong> en bas) et il vous sera présenté un exemple de lignes tarifaires pour le transporteur <strong>" + this.value + "</strong></p>").insertAfter(this);
      jQuery('#woocommerce_'+cdimethod+'_title').val("CDI "+carriers[carrier]); 
      jQuery('#woocommerce_'+cdimethod+'_prefixshipping').val( carriers[carrier] + ' '); 
      var start = 'CDI-shipping-defaut-'+ carrier + '.php' ;
      jQuery('#woocommerce_'+cdimethod+'_shippingdefaulttariffsfile').val(start);       
    }
  });
});

// Shipping CDI shipping table
jQuery(function() {
if (typeof cdicurrentpackageid !== "undefined") {
  jQuery('#' + cdicurrentpackageid + '_table_rates').on('click', 'a.add', function() {
    var size = jQuery('#' + cdicurrentpackageid + '_table_rates tbody .table_rate').size();
    var previous = size - 1;
    jQuery('\
      <tr class="table_rate">\
	<th class="check-column"><input type="checkbox" name="select" /></th>\
	  <td><input type="text" name="' + cdicurrentpackageid + '_tablerate[' + size + '][method_name]" style="width: 90%; min-width:100px" class="' + cdicurrentpackageid + 'field[' + size + ']" placeholder="" size="4" /></td>\
	  <td><input type="number" step="any" min="0" name="' + cdicurrentpackageid + '_tablerate[' + size + '][fare]" style="width: 90%; min-width:75px" class="' + cdicurrentpackageid + 'field[' + size + ']" placeholder="0.00" size="4" /></td>\
	  <td><input type="text" name="' + cdicurrentpackageid + '_tablerate[' + size + '][addfees]" style="width: 90%" class="' + cdicurrentpackageid + 'field[' + size + ']" placeholder="" size="4" /></td>\
	  <td><select multiple size="2" name="' + cdicurrentpackageid + '_tablerate[' + size + '][class]">' + cdishipclass + '</select></td>\
	  <td><select name="' + cdicurrentpackageid + '_tablerate[' + size + '][methods]">' + cdioptions + '</select></td>\
	  <td><input type="number" step="any" min="0" name="' + cdicurrentpackageid + '_tablerate[' + size + '][pricemin]" style="width: 90%; min-width:75px" class="' + cdicurrentpackageid + 'field[' + size + ']" placeholder="0.00" size="4" /></td>\
	  <td><input type="number" step="any" min="0" name="' + cdicurrentpackageid + '_tablerate[' + size + '][pricemax]" style="width: 90%; min-width:75px" class="' + cdicurrentpackageid + 'field[' + size + ']" placeholder="0.00" size="4" /></td>\
	  <td><input type="number" step="any" min="0" name="' + cdicurrentpackageid + '_tablerate[' + size + '][weightmin]" style="width: 90%; min-width:75px" class="' + cdicurrentpackageid + 'field[' + size + ']" placeholder="0.00" size="4" /></td>\
	  <td><input type="number" step="any" min="0" name="' + cdicurrentpackageid + '_tablerate[' + size + '][weightmax]" style="width: 90%; min-width:75px" class="' + cdicurrentpackageid + 'field[' + size + ']" placeholder="0.00" size="4" /></td>\
        </tr>').appendTo('#' + cdicurrentpackageid + '_table_rates table tbody');
    return false;
  });
  // Remove row
  jQuery('#' + cdicurrentpackageid + '_table_rates').on('click', 'a.remove', function() {
    var answer = confirm("Suppression des tarifs sélectionnés ?")
    if (answer) {
      jQuery('#' + cdicurrentpackageid + '_table_rates table tbody tr th.check-column input:checked').each(function(i, el) {
        jQuery(el).closest('tr').remove();
      });
    }
    return false;
  });
}
});	

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
			
// Blink function on CDI Information
jQuery(document).ready(function(){ 
  jQuery( "#blink" ).each(function( index ) { 
    var blacktime = 1000;
    var whitetime = 1000;
    setTimeout(whiteFunc,blacktime);
    function whiteFunc(){
      document.getElementById("blink").style.color = "white";
      setTimeout(blackFunc,whitetime);
    }
    function blackFunc(){
      document.getElementById("blink").style.color = "black";
      setTimeout(whiteFunc,blacktime);
    }
  });
});			



