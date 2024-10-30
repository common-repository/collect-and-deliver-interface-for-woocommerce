
//Global var :
// cdiurlglobeopen
// cdiurlglobeclose
// cdiajaxurl
// cdiwhereselectorpickup

jQuery(document).ready(function(){

function openclosemap(){ 
  if (jQuery('#cdimapcontainer').length){  
    jQuery('html, body').animate({ scrollTop: jQuery("#cdimapcontainer").offset().top  - 32}, 2000); 
    var urlglobeopen = cdiurlglobeopen ;
    jQuery("#popupmap").html(' ') ;
    jQuery("#pickupgooglemaps" ).attr('src', urlglobeopen) ;  
  }else{   
    var data = { 'action': 'set_pickupgooglemaps', 'pickupgooglemaps': 'pickupgooglemaps' };
    var ajaxurl = cdiajaxurl ;
    jQuery.post(ajaxurl, data, function(response) {
      var urlglobeclose = cdiurlglobeclose ;
      jQuery("#popupmap").html(response) ;
      jQuery("#pickupgooglemaps" ).attr('src', urlglobeclose) ;     
      if (jQuery('#cdimapcontainer').length){
        jQuery('html, body').animate({ scrollTop: jQuery("#cdimapcontainer").offset().top  - 32}, 2000);
      }
    });
  }
}

jQuery(document).on("click", "#cdiclickearth", function(clickoniconmap){
  openclosemap()
  clickoniconmap.preventDefault(); 
});

jQuery(document).on("change", "#pickupselect", function(selectoptions){
  var pickupselect = document.getElementById("pickupselect").options[document.getElementById("pickupselect").selectedIndex].value; ;  
  var data = { "action": "set_pickuplocation", "postpickupselect": pickupselect };
  var ajaxurl = cdiajaxurl ;
  jQuery.post(ajaxurl, data, function(response) {
    var arrresponse = jQuery.parseJSON(response);
    if (arrresponse[0].length){ // No display if no return
      var html = arrresponse[0].includes("<", 0);
      if (arrresponse[0].includes("</", 0)) { // Is return a html code ?
        var para = document.createElement("DIV"); 
        para.setAttribute("id", "customselect");
        para.style.position = "fixed"; 
        para.style.width = "80vw";
        para.style.height = "80vh";
        para.style.right = "10vw";
        para.style.top = "10vh";
        document.body.appendChild(para);    
        jQuery("#customselect").html(arrresponse[0]) ;
      }else{ // Not html, so display with alert
        alert(arrresponse[0]);
      }
    }
    if (jQuery("#cdimapcontainer").length){ // Refresh google maps if open
      var urlglobeclose = cdiurlglobeclose ;
      jQuery("#popupmap").html(arrresponse[1]) ;
      jQuery("#pickupgooglemaps" ).attr("src", urlglobeclose) ;
      jQuery('html, body').animate({ scrollTop: jQuery("#cdimapcontainer").offset().top  - 32}, 2000); 
    }
  });
});
if (jQuery('#cdimapcontainer').length){
  jQuery('html, body').animate({ scrollTop: jQuery("#cdimapcontainer").offset().top  - 32}, 2000);
}  
});
