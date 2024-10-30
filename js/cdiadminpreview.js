// CDI Gateway Preview
function createthebox() {
  jQuery("#cdiboxjs").each(function(){
    var element = document.getElementById("cdiboxjs");
    element.parentNode.removeChild(element);
  });
  var para = document.createElement("DIV"); 
  para.setAttribute("id", "cdiboxjs");
  para.setAttribute("class", "cdiboxjs"); 
  para.style.position = "fixed"; 
  para.style.maxWidth = "40vw"; 
  para.style.right = "2vw";
  para.style.top = "5vh";
  para.style.border = "5px solid blue";
  para.style.backgroundColor = "white";
  para.style.color = "black";
  para.style.padding = "15px";
  document.body.appendChild(para);
}

jQuery(document).ready(function(){
  jQuery(".cdi-preview-metabox").click(function(){
    var elName = this.name;
    var elOrder = elName.replace("cdi_preview_metabox", "");
    var data = { 'action': 'cdi_ajax_gateway', 'case': '4', 'code': elOrder };
    jQuery.post(ajaxurl, data, function(response) {
      var arrresponse = jQuery.parseJSON(response);
      createthebox() ;
      retour = "<button id='close' style='float:right' onclick='this.parentNode.parentNode.removeChild(this.parentNode); return false;'>X</button><br>" ;
      retour = retour.concat("<header style='display:inline-block;'><h1 style='display:inline-block;'><strong>Aperçu Métabox : " + elOrder + " </strong></h1><mark style='display:inline-block;'> " + arrresponse[2] + " </mark></header>") ;
      retour = retour.concat(arrresponse[1]) ;
      jQuery("#cdiboxjs").html(retour) ;
    });
  });
});

jQuery(document).ready(function(){
  var obj = cdiarrhtmljs ;
  jQuery(".cdi-preview-order").click(function(){
    createthebox() ;
    var elName = this.name;
    var elOrder = elName.replace("cdi_preview_", "");
    retour = "<button id='close' style='float:right' onclick='this.parentNode.parentNode.removeChild(this.parentNode); return false;'>X</button><br>" ;
    jQuery("#cdiboxjs").html(retour + obj[elOrder]) ;
  });
});

jQuery(document).ready(function(){
  var elName ;
  var elOrder ;
  var newadrstructure ; 
  var obj = cdiarrhtmlshipadjs ;
  var retour ;
  function htmlheader() {
    retour = "<button id='close' style='float:right' onclick='this.parentNode.parentNode.removeChild(this.parentNode); return false;'>X</button><br>" ;
    retour = retour.concat("<header style='display:inline-block;'><h1 style='display:inline-block;'><strong>Destination Colis : " + elOrder + " </strong></h1><mark style='display:inline-block;'> " + obj[elOrder]['st'] + " </mark></header>") ;
    retour = retour.concat("<h2><strong>Adresse de livraison : </strong></h2>") ;
    return retour ;
  }
  function htmllignesad() {
    var ad = '' ;
    if  (obj[elOrder]["l1"] !== '') {ad = ad.concat(obj[elOrder]["l1"] + "<br>"); } 
    if  (obj[elOrder]["l2"] !== '') {ad = ad.concat(obj[elOrder]["l2"] + "<br>"); } 
    if  (obj[elOrder]["l3"] !== '') {ad = ad.concat(obj[elOrder]["l3"] + "<br>"); }
    if  (obj[elOrder]["l4"] !== '') {ad = ad.concat(obj[elOrder]["l4"] + "<br>"); } 
    return ad ;
  }
  function htmlendad() {
    return obj[elOrder]["cp"] + " " + obj[elOrder]["vi"] + "<br>" + obj[elOrder]["pa"]  ;
  }
  function listedelignes() {
    var data = { 'action': 'cdi_ajax_gateway', 'case': '1', 'address': obj[elOrder] };
    jQuery.post(ajaxurl, data, function(response) {
      var arrresponse = jQuery.parseJSON(response);
      var html = htmlheader() + htmllignesad() + htmlendad();
      if (arrresponse[0] == "ok") {
        var tab = jQuery.parseJSON(arrresponse[1]);
        var ret = '<br>' ;
        for (var i = 0; i < Object.keys(tab).length; ++i) {
          var ligne = Object.values(tab)[i];
          var code = ligne.code;
          var adresse = ligne.adresse;
          ret = ret.concat('<br><strong><button class="cdiadrline" name="cdiadrline_' + code + '" style="max-width:40vw;">' + adresse + '</button></strong><br>');  
        }
        ret = ret.concat("<br>Les adresses ci-dessus vous sont proposées comme choix. Si l'une vous convient mieux que l'adresse de livraison actuelle, cliquez dessus puis confirmez ; cette adresse sera alors mise à jour dans la commande Wooommerce.<br>");  
        var html = html.concat(ret + '<br>') ;
        jQuery("#cdiboxjs").html(html);
        // Rattachement dynamique d'évènement
        jQuery( ".cdiadrline" ).each(function( index ) { 
          var element = this;
          element.onclick = choixdeligne ;
	});
      }else{
        var html = html.concat(arrresponse[1]) ;
        jQuery("#cdiboxjs").html(html);
      }
    });
  }
  function choixdeligne() {
    var elNameLi = this.name;
    var elAdrcode = elNameLi.replace("cdiadrline_", "");
    var data = { 'action': 'cdi_ajax_gateway', 'case': '2', 'code': elAdrcode };
    jQuery.post(ajaxurl, data, function(response) {
      var arrresponse = jQuery.parseJSON(response);
      var html = htmlheader() + htmllignesad() + htmlendad();
      var textinfo = '<br>' ;
      textinfo = textinfo.concat("<br>A votre confirmation, l'adresse de livraison ci-dessus sera remplacée par l'adresse ci-dessous dans la commande Woocommerce.<br>");  
      textinfo = textinfo.concat("<br><strong>Nouvelle adresse proposée : </strong>") ;
      newadrstructure = arrresponse[1];
      var newadrhtml = arrresponse[2];
      confirm = "<br><strong><button id='cdiadrconfirm' style='float:right'>Confirmer</button></strong><br>" ;
      var html = html.concat(textinfo + '<br>') ;
      var html = html.concat(newadrhtml + '<br>') ;
      var html = html.concat(confirm + '<br>') ;
      jQuery("#cdiboxjs").html(html);
      // Rattachement dynamique d'évènement
      jQuery( "#cdiadrconfirm" ).each(function( index ) { 
        var element = this;
        element.onclick = confirmation ;
	      });
    });
  }
  function confirmation() {
    var data = { 'action': 'cdi_ajax_gateway', 'case': '3', 'order': elOrder, 'newadr': newadrstructure, };   
    jQuery.post(ajaxurl, data, function(response) {
      location.reload();
    });
  }
  function modiflignes() {
    var html = htmlheader() + htmllignesad() + htmlendad();
    var ret = '<br>' ;
    ret = ret.concat("<br>Modifiez les lignes puis enregistrez ; cette adresse sera alors mise à jour dans la commande Wooommerce.<br>");  
    ret = ret.concat("<form id='formmodiflignes'>"
            + '<p>L1 : <input id="ligne1" value="' + obj[elOrder]["l1"] + '">Numéro de la voie + Nom de la voie (rue, avenue, ...)<p>'
            + '<p>L2 : <input id="ligne2" value="' + obj[elOrder]["l2"] + '">Entrée - Bloc - Batiment - Résidence<p>'
            + '<p>L3 : <input id="ligne3" value="' + obj[elOrder]["l3"] + '">Appartement ou boite aux lettres - Couloir - Escalier<p>'
            + '<p>L4 : <input id="ligne4" value="' + obj[elOrder]["l4"] + '">Boite postale - Lieu dit<p>'
            + '<p>CP : <input id="postcode" value="' + obj[elOrder]["cp"] + '"><p>'
            + '<p>Commune : <input id="city" value="' + obj[elOrder]["vi"] + '"><p>'
          + "</form> ");  
    enregistre = "<br><strong><button id='cdiadrenregistrer' style='float:right'>Enregistrer</button></strong><br>" ;
    var html = html.concat(ret + '<br>') ;
    var html = html.concat(enregistre + '<br>') ;
    jQuery("#cdiboxjs").html(html);
    // Rattachement dynamique d'évènement
    jQuery( "#cdiadrenregistrer" ).each(function( index ) { 
      var element = this;
      element.onclick = cdiadrenreg ;
	    });
  }
  function cdiadrenreg() {
    newadrstructure = { 'l1': jQuery('#formmodiflignes #ligne1').val(), 'l2': jQuery('#formmodiflignes #ligne2').val(), 'l3': jQuery('#formmodiflignes #ligne3').val(), 'l4': jQuery('#formmodiflignes #ligne4').val(), 'cp': jQuery('#formmodiflignes #postcode').val(), 'vi': jQuery('#formmodiflignes #city').val() };
    var data = { 'action': 'cdi_ajax_gateway', 'case': '3', 'order': elOrder, 'newadr': newadrstructure, };    
    jQuery.post(ajaxurl, data, function(response) {
      location.reload();
    });
  }
  jQuery(".cdi-checkad").click(function(){
    elName = this.name;
    elOrder = elName.replace("cdi_checkad_", "");
    // Création DIV contenante
    createthebox() ;
    htmlgo = "<br>"
             + "<br>L'adressage étendu à 4 lignes n'est généralisé dans Woocommerce que si l'option correspondante a été sélectionnée dans les réglagees de CDI.<br>"
             + "<br><strong>Pour modifier l'adresse de livraison, cliquez sur le bouton 'Modifier' ci-dessous.</strong><br>"
             + "<br><strong>Pour faire vérifier cette adresse par La Poste, cliquez sur le bouton 'Vérifier' ci-dessous.</strong><br>"
             + "Vous devez avoir renseigné votre clé API de La Poste (https://developer.laposte.fr/) dans les réglages de CDI pour que ce contrôle fonctionne. Par ailleurs, sachez que La Poste n'assure ce service que pour certaines destinations et non pour d'autres.<br>"
             + "<br><strong><button id='cdiadrmodif' style='float:left'>Modifier</button></strong><strong><button id='cdiadrcontrol' style='float:right'>Vérifier</button></strong><br>" ;
    jQuery("#cdiboxjs").html(htmlheader() + htmllignesad() + htmlendad() + htmlgo );
    // Rattachement dynamique d'évènement
    jQuery( "#cdiadrcontrol" ).each(function( index ) { 
        var element = this;
        element.onclick = listedelignes ;
	    });
    jQuery( "#cdiadrmodif" ).each(function( index ) { 
        var element = this;
        element.onclick = modiflignes ;
	    });
  });
});

