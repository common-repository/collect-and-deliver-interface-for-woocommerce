
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


