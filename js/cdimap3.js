jQuery(document).ready(function(){

jQuery(document).on("click", "a.selretrait.button", function(detailselret){
  document.getElementById("selretraithidden").style.display = "inline";
  document.getElementById("selretraitshown").style.display = "none";
  var selretrait_id = document.getElementById('selretrait');
  var idret = selretrait_id.className;       
  idret = idret.substring(13); // sup begin of class name "cdiselretrait"     
  var options = document.querySelector("#pickupselect").options; 
  for (var i = 0; i < options.length; i++) { 
    if (options[i].value == idret) {
      var pickupselectvalue =  options[i].value;
      var pickupselecttext =  options[i].text;
      options[i].selected = true;       
      var sel = document.getElementById('pickupselect');
      fireEvent(sel,'change'); 
      break;
    }
  }
  function fireEvent(element,event){      
    if (document.createEventObject){ 
      var evt = document.createEventObject();  
      return element.fireEvent('on'+event,evt)   
    }else{ 
      var evt = document.createEvent("HTMLEvents");      
      evt.initEvent(event, true, true ); 
      return !element.dispatchEvent(evt);
    }
  }
  //suppopover();
  jQuery('html, body').animate({ scrollTop: jQuery("#cdimapcontainer").offset().top  - 32}, 2000); 
}); 

}); 
