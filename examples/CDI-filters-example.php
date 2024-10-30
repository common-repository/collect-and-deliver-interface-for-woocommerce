<?php

/**
 * This file is part of the CDI - Collect and Deliver Interface plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('ABSPATH')) exit;

//
// *** Simple examples to show use of CDI filters for customization 
//

  //****** Model action hook to custom the div cdiselectlocation
  add_action( 'wp_footer','example_transform_cdiselectlocation') ; 
  function example_transform_cdiselectlocation () {
    ?> <style>
      .cdiselectlocation {display: inline-block;  width:100%; }   
      #popupmap {display: inline-block;  float:left; width: 500px;} 
      #pickupselect {display: inline-block; float:left; width: 500px;}  
    </style><?php
  }

  //****** Model filter to use radio button selection
  add_filter( 'cdi_filterhtml_retrait_selectoptions','example_cdi_filterhtml_retrait_selectoptions', 10, 2) ; 
  function example_cdi_filterhtml_retrait_selectoptions ($insertselect, $listePointRetraitAcheminement) {
    $newhtml = '<p><div id="pickupselect" style="border:2px solid #539992; border-radius:25px; height:330px; padding:10px;"><div style="height:310px; overflow:scroll; color:#539992;">';
    foreach ($listePointRetraitAcheminement as $PointRetrait) {
      $newhtml .= '<input type="radio" id="ipickupselect" name="ipickupselect" value=' . $PointRetrait->identifiant . '>' . $PointRetrait->nom .  ' à ' . $PointRetrait->distanceEnMetre . 'm' . '<br>' ;
    }
    $newhtml .= '</div></div></p>' ;
    return $newhtml; 
  }

  //****** Model filter to get the value of the selected radio button
  add_filter( 'cdi_filterjava_retrait_selectorpickup','example_cdi_filterjava_retrait_selectorpickup') ;
  function example_cdi_filterjava_retrait_selectorpickup ($jsselectorpickup) {
    return 'var radioButtons = document.getElementsByName("ipickupselect"); for (var i = 0; i < radioButtons.length; i++) { if (radioButtons[i].checked) { var pickupselect =  radioButtons[i].value; } }' ;
  }

  //****** Model filter to change settings for the google map
  add_filter( 'cdi_filterarray_retrait_mapparam','example_cdi_filterarray_retrait_mapparam') ;
  function example_cdi_filterarray_retrait_mapparam ($paramgooglemapcss) {
    return array('z'=>"12", 'w'=>"100%", 'h'=>"450px", 'maptype' => 'ROAD', 'style' => 'border:2px solid #539992; margin: 0 auto; border-radius: 25px;', 'styles' => '[{stylers: [{ hue: "#00ffe6" },      { saturation: -20 }] },{ featureType: "road", elementType: "geometry", stylers: [{ lightness: 100 }, { visibility: "simplified" }] },{ featureType: "road", elementType: "labels", stylers: [{ visibility: "off" }] }]') ;
  }

  //****** Model filter to change the detail display for the selected location
  add_filter( 'cdi_filterhtml_retrait_displayselected','example_cdi_filterhtml_retrait_displayselected', 10, 2) ;
  function example_cdi_filterhtml_retrait_displayselected ($pickupdetail, $PointRetrait) {
    $pickupdetail = str_replace('  ','..',$pickupdetail) ; $pickupdetail = '<p>' . str_replace("\x0a",'</p><p style="margin-bottom:0px;">',$pickupdetail) . '</p>' ;
    $return = '<div id="divtodetail" style="background-color:#eeeeee; color:#539992; width:400px; padding:10px; position:fixed; top:30%; left:calc(50vw - 200px); border:2px solid #539992; border-radius: 25px;">' . $pickupdetail . '</div>'; // define html display
    $return .= '<script> jQuery(document).ready(function(){ jQuery("#customselect").click(function(detailpickupremove){ jQuery("#customselect").remove(); }); }); </script>' ; // clean of popup div
    return $return ;
  }

  //****** Model filter to change the markers icon file
  add_filter( 'cdi_filterurl_retrait_iconmarker','example_cdi_filterurl_retrait_iconmarker') ;
  function example_cdi_filterurl_retrait_iconmarker ($url) {
    return plugins_url( 'images/iconvert.png', dirname(__FILE__)) ;
  }

  //****** Model filter to change the display for the selected location
  add_filter( 'cdi_filterhtml_retrait_descpickup','example_cdi_filterhtml_retrait_descpickup', 10, 2) ;
  function example_cdi_filterhtml_retrait_descpickup ($description, $PointRetrait) {
    return '<div><p style="color:red; margin-bottom:0px;">(' . $PointRetrait->identifiant . ')</p><p style="color:green; margin-bottom:0px;">' . $PointRetrait->nom . '</p><p style="color:green; margin-bottom:0px;">' .  $PointRetrait->adresse1 . ' ' . $PointRetrait->adresse2 . '</p></div>' ;
  }

  //****** Model filter to change the customer location description on the map
  add_filter( 'cdi_filterhtml_retrait_desccustomer','example_cdi_filterhtml_retrait_desccustomer', 10, 2) ;
  function example_cdi_filterhtml_retrait_desccustomer ($desccustomer, $customer) { // Last argument customer is now an objet
    return 'Your home location !' ;
  }

  //****** Model filter to change the shipping icon file
  add_filter( 'cdi_filterurl_shipping_icon','example_cdi_filterurl_shipping_icon', 10, 2) ;
  function example_cdi_filterurl_shipping_icon ($url, $idmethod) {
    return plugins_url( 'images/icontest.png', dirname(__FILE__)) ;
  }

  //****** Model filter to change a shipping rate
  add_filter( 'cdi_filterarray_shipping_rate','example_cdi_filterarray_shipping_rate') ;
  function example_cdi_filterarray_shipping_rate ($rate) {
    global $woocommerce;
    $newrate = $rate ;
    if (explode(':', $rate['id'])[0] == 'cdi_shipping_colissimo_home5') {
      //Compute and change $newrate['label'],$newrate['cost'], $newrate['calc_tax']
      $pricehtcart = (float)$woocommerce->cart->cart_contents_total;
      $weightcart = (float)$woocommerce->cart->cart_contents_weight;
      $newrate['cost'] =  floor($pricehtcart/150)*0.9 ;
    }
    return $newrate;
  }
  
  //****** Model filter to modify the apparent weight and/or price of shipping basket, in view to modify the shipping calculation.
  //****** Actual cart data will not be modified when shipping by the carrier. 
  add_filter( 'cdi_filterarray_shipping_altercartview','example_cdi_filterarray_shipping_altercartview', 10,3) ;
  function example_cdi_filterarray_shipping_altercartview ($altercartview, $package, $currentpackage) {
	// Example to neutralize the weight added by a specific product class.
	global $woocommerce;
	$newweight = $altercartview['weight'] ;
	$classtosbstract = "class-to-substract-from-calculation" ; // Class to substract from weight shipping computation
	$weighttosubstract = 0;
	$found_shipping_classes = array();
	foreach ( $package['contents'] as $item_id => $values ) {
		if ( $values['data']->needs_shipping() ) {
			$found_class = $values['data']->get_shipping_class();		
			if ($found_class == $classtosbstract) {						
				$product_id = $values['product_id'];
				$variation_id = $values['variation_id'];
				$quantity = $values['quantity'];
				
				$product = wc_get_product($product_id) ;
				$weight = $product->get_weight() ;
				$variation = wc_get_product($variation_id) ;
				if ($variation) {
					$weight = $variation->get_weight() ;
				}				
				$weighttosubstract = $weighttosubstract + ($weight * $quantity);
 				$newweight = $newweight - $weighttosubstract ;
			}
		}
	}
	$altercartview['weight'] = $newweight ;
	return $altercartview;
  }

  //****** Model filter to change the mobile phone number when in automatic mode - Web service
  add_filter( 'cdi_filterstring_auto_mobilenumber','example_cdi_filterstring_auto_mobilenumber', 10, 2) ;
  function example_cdi_filterstring_auto_mobilenumber ($MobileNumber, $order_id) {
    $newMobileNumber = $MobileNumber ;
    //Search mobile number of the customer 
    return $newMobileNumber;
  }

  //****** Model filter to custom initial datas before CDI metabox creation
  add_filter( 'cdi_filterarray_orderlist_before_metabox','example_cdi_filterarray_orderlist_before_metabox', 10, 3) ;
  function example_cdi_filterarray_orderlist_before_metabox ($arrayinitmetabox, $order, $valueshippingmethod) {
    //Change datas in $arrfilter array to be put in metabox
    //Example compute and change product code and insurance depending on order cart and shipping method chosen
    $arrayinitmetabox['_cdi_meta_productCode'] = 'DOS' ;
    $arrayinitmetabox['_cdi_meta_additionalcompensation'] = 'yes' ;
    $arrayinitmetabox['_cdi_meta_amountcompensation'] = 300 ;
    return $arrayinitmetabox;
  }

  //****** Model filter to set compensation amount according to CDI insurance level before metabox creation
  add_filter( 'cdi_filterarray_orderlist_before_metabox','example_cdi_filterarray_orderlist_before_metabox_2', 10, 3) ;
  function example_cdi_filterarray_orderlist_before_metabox_2 ($arrayinitmetabox, $order, $valueshippingmethod) {
    global $woocommerce;
    $shipping_method = @array_shift($order->get_shipping_methods());
    $shipping_method_title = $shipping_method['method_title'];
    if ($shipping_method_title == 'Colissimo France avec assurance') {
      $price = $order->get_subtotal();
      $montantassurance = ceil($price/150)*150;
      if ($montantassurance > 150000){
        $montantassurance = 150000;
      }
      $arrayinitmetabox['_cdi_meta_signature'] = 'yes' ;
      $arrayinitmetabox['_cdi_meta_additionalcompensation'] = 'yes' ;
      $arrayinitmetabox['_cdi_meta_amountcompensation'] = $montantassurance ;
    }
    return $arrayinitmetabox;
  }

  //****** Model filter to custom initial cn23 before CDI metabox creation
  add_filter( 'cdi_filterarray_orderlist_before_metaboxcn23','example_cdi_filterarray_orderlist_before_metaboxcn23', 10, 3) ;
  function example_cdi_filterarray_orderlist_before_metaboxcn23 ($arrayinitmetabox, $order, $valueshippingmethod) {
    //Change datas in $arrfilter array to be put in metabox
    //Example change category and shipping
    $arrayinitmetabox['_cdi_meta_cn23_shipping'] = '12.34' ;
    $arrayinitmetabox['_cdi_meta_cn23_category'] = '1' ;
    return $arrayinitmetabox;
  }

  //****** Model filter to custom cn23 articles before CDI metabox creation
  add_filter( 'cdi_filterarray_orderlist_before_metaboxcn23art','example_cdi_filterarray_orderlist_before_metaboxcn23art', 10, 4) ;
  function example_cdi_filterarray_orderlist_before_metaboxcn23art ($arrayinitmetabox, $order, $valueshippingmethod, $item) {
    //Change datas in $arrfilter array to be put in metabox
    //Example change hstariffnumber
    $arrayinitmetabox['_cdi_meta_cn23_article_hstariffnumber'] = '123456' ;
    return $arrayinitmetabox;
  }
  
  //****** Model filter 2 to custom Hstariff articles before CDI metabox creation
  add_filter( 'cdi_filterarray_orderlist_before_metaboxcn23art','example2_cdi_filterarray_orderlist_before_metaboxcn23art', 10, 4) ;
  function example2_cdi_filterarray_orderlist_before_metaboxcn23art ($arrayinitmetabox, $order, $valueshippingmethod, $item) {
 	//Change datas in $arrfilter array to be put in metabox
 	//Example to pick-up hstariffnumber in product to force in order items
	global $woocommerce;
	$product_id = $item['product_id'];
	$product = wc_get_product( $product_id );
	$attributes = $product->get_attributes() ;	  				
	foreach ( $attributes as $attribute ) {
		$attribute_name = $attribute->get_name();						
    		if ($attribute_name == 'hstariff') { // Check if it's the right taxo you are looking for
			$attribute_value = $attribute->get_options()[0]; // Only the first item in array
			$arrayinitmetabox['_cdi_meta_cn23_article_hstariffnumber'] = $attribute_value ; // Update item order with data you want			
			break ;
    		}
  	}
	return $arrayinitmetabox;
  }
  
  //****** Model filter to set retour-colis eligible only if WC status is in status "completed" or in private status "livre"
  add_filter( 'cdi_filterstring_retourcolis_eligible','example_cdi_filterstring_retourcolis_eligible', 10, 2) ;
  function example_cdi_filterstring_retourcolis_eligible ($eligible, $order) {
    if ($order->get_status() == 'completed' OR $order->get_status() == 'livre') {
      $eligible = 'yes' ;
    }else{
      $eligible = 'no' ;
    }
    return $eligible;
  }
  
  //****** Model filter to force retour-colis eligible if WC status is in status "completed" or in private status "livre", and regardless of what the cdi status is
  add_filter( 'cdi_filterstring_retourcolis_eligible_force','example_cdi_filterstring_retourcolis_eligible_force', 10, 2) ;
  function example_cdi_filterstring_retourcolis_eligible_force ($eligible, $order) {
    if ($order->get_status() == 'completed' OR $order->get_status() == 'livre') {
      $eligible = 'yes' ;
    }else{
      $eligible = 'no' ;
    }
    return $eligible;
  }

  //****** Model filter to trigger update of order CDI metabox with gateway data when WC status is also in a private status
  add_filter( 'cdi_filterstring_orderlist_eligible','example_cdi_filterstring_orderlist_eligible', 10, 2) ;
  function example_cdi_filterstring_orderlist_eligible ($eligible, $order) {
    if ($order->get_status() == 'privatestatus') {
      $eligible = 'yes' ;
    }
    return $eligible;
  }

  //****** Model 1 filter to change the company+order-id to be displayed in auto, coliship, and online gateway
  add_filter( 'cdi_filterstring_gateway_companyandorderid','example_cdi_filterstring_gateway_companyandorderid1', 10, 2) ;
  function example_cdi_filterstring_gateway_companyandorderid1 ($companyorderid, $array_for_carrier) {
    //Example : change with the woocommerce shipping_company field
    $newcompany = $array_for_carrier['shipping_company'] ;
    return $newcompany;
  }

  //****** Model 2 filter to change the company+order-id to be displayed in auto, coliship, and online gateway
  add_filter( 'cdi_filterstring_gateway_companyandorderid','example_cdi_filterstring_gateway_companyandorderid2', 10, 2) ;
  function example_cdi_filterstring_gateway_companyandorderid2 ($companyorderid, $array_for_carrier) {
    //Example : Set the order number in place of order id
    global $woocommerce ; 
    $order_id = $array_for_carrier['order_id'] ;
    $order = new WC_Order($order_id);
    $order_number = $order->get_order_number();
    $newcompany = $array_for_carrier['shipping_company'] . ' - ' . $order_number ;
    return $newcompany;
  }

  //****** Model filter to sort the parcels array in the gateway
  add_filter( 'cdi_filterarray_gateway_sortresults','example_cdi_filterarray_gateway_sortresults') ;
  function example_cdi_filterarray_gateway_sortresults ($results) {
    //Example : sort by id number
    function cdi_customsort($a, $b) { return strcmp($a->cdi_order_id, $b->cdi_order_id); }
    usort($results, "cdi_customsort");
    return $results;
  }

  //****** Model filter to custom infos display in the gateway destination field
  add_filter( 'cdi_filterstring_gateway_displayorder','example_cdi_filterstring_gateway_displayorder', 10, 2) ;
  function example_cdi_filterstring_gateway_displayorder ($displayorder, $array_for_carrier) {
    $displayorder = $array_for_carrier['shipping_first_name'] . ' ' . $array_for_carrier['shipping_last_name'] ;
    return $displayorder;
  }

  //****** Model filter to change the place where the pickupselect will be
  add_filter( 'cdi_filterjava_retrait_whereselectorpickup','example_cdi_filterjava_retrait_whereselectorpickup') ;
  function example_cdi_filterjava_retrait_whereselectorpickup ($whereselectorpickup) {
    //Example to place pickup selector and Googlemap after the shop_table and before the payment div
    return 'insertAfter( ".shop_table" )' ;
  }

  //****** Model filter to change the label_data to be displayed in printlabel mode
  add_filter( 'cdi_filterhtml_printlabel_labeldata','example_cdi_filterhtml_printlabel_labeldata', 10, 2) ;
  function example_cdi_filterhtml_printlabel_labeldata ($labeldata, $array_for_carrier) {
    //Example : soulignement adresse
    $newlabeldata = $labeldata . '<div class="clearb"></div><hr/>' ;
    return $newlabeldata;
  }

  //****** Model action to automatically change woocommerce order status after a tracking code have been set in CDI gateway
  add_action( 'cdi_actionorderlist_after_updateorder', 'example_cdi_actionorderlist_after_updateorder', 10,1 );
  function example_cdi_actionorderlist_after_updateorder( $order ) {
    //Example : pass woocommerce order status to 'completed'
    global $woocommerce;
    $order = new WC_Order($order);
    $order_status = $order->get_status();
    $order->update_status('completed');
  }

  //****** Model filter for WPML plugin. When WC multi shipping packages (i.e. Market places) and WPML translation plugin (doing products exchange) are used simultaneously. This filter is called in Admin - WC orders list
  add_filter( 'cdi_filterarray_itemslist_ordered_shippingpackage','example_cdi_filterarray_itemslist_ordered_shippingpackage', 10, 3) ;
  function example_cdi_filterarray_itemslist_ordered_shippingpackage ($items, $chosen_products, $order) {
    global $sitepress;
    if(class_exists('SitePress')) {
      $returnitems = array();
      $order_language = $order->get_meta('wpml_language');
      $language_to_filter = $order_language ? $order_language : $sitepress->get_current_language();
      foreach( $items as $item ) {
        $translate_product_id = apply_filters( 'translate_object_id', $item['product_id'], 'product', false, $language_to_filter );
        if (in_array($translate_product_id, $chosen_products)) { // To ensure to get only products in shipping package
          $returnitems[] = $item ;
        }
      }
    }else{
      $returnitems = $items;
    }
    return $returnitems;
  }

  //****** Model filter for Belgium and Switzerland address line 2, which does not exist in this countries
  add_action( 'cdi_filterarray_auto_arrayforcarrier', 'example_cdi_filterarray_auto_arrayforcarrier', 10,1 );
  function example_cdi_filterarray_auto_arrayforcarrier( $array_for_carrier ) {
    $return = $array_for_carrier ;
    if ($return['shipping_country'] == 'BE' OR $return['shipping_country'] == 'CH') {
      $return['shipping_address_1'] = $return['shipping_address_1'] . ' ' . $return['shipping_address_2'] ;
    }
    return $return ;
  }

  //****** Model filter to change the "sender parcel ref" in Array_for_carrier
  add_action( 'cdi_filterstring_sender_parcel_ref', 'example_cdi_filterstring_sender_parcel_ref', 10,1 );
  function example_cdi_filterstring_sender_parcel_ref( $sender_parcel_ref ) {
    //Example to extend the ref with "order_id(order_number)" 
    global $woocommerce ; 
    $order_id = $sender_parcel_ref ; // The defaut ref is order_id (it is also the parcel id)
    $order = new WC_Order($order_id);
    $order_number = $order->get_order_number();
    $return = $order_id . ' (' . $order_number . ')';
    return $return ;
  }

  //****** Model filter to change the "sender parcel ref" in Array_for_carrier
  add_action( 'cdi_filterstring_carrier_instructions', 'example_cdi_filterstring_carrier_instructions', 10,1 );
  function example_cdi_filterstring_carrier_instructions( $carrier_instructions ) {
    $return = 'instruction-to-carrier-example' ;
    return $return ;
  }
  
  //****** Model filter to adapt address lines before request for label of a specific carrier
  add_filter( 'cdi_filterarray_auto_arrayforcarrier','example2_cdi_filterarray_auto_arrayforcarrier') ;
  function example2_cdi_filterarray_auto_arrayforcarrier ($array_for_carrier) {
    // CDI filter always has 4 address lines as entry
    //Example to adapt for Mondial Relay which has only 3 lines of 32 digits
    $new_array_for_carrier = $array_for_carrier ;
    if ($array_for_carrier['carrier'] == 'mondialrelay') {
      $l1 = $new_array_for_carrier['shipping_address_1'] ;
      $l2 = $new_array_for_carrier['shipping_address_2'] ;     
      $l3 = $new_array_for_carrier['shipping_address_3'] ;  
      $l4 = $new_array_for_carrier['shipping_address_4'] ;
      if ((strlen(l1) > 32) or (strlen(l2) > 32)) {
        // Do a warning for the admin who must manually reorg this address and retry
        $new_array_for_carrier = 'Error in address encounter when processing filter cdi_filterarray_auto_arrayforcarrier' ;
        //    OR
        // parse the 4 lines and reorg them to have no more than 32 digits in each line
        // then reaffect $l1 to $new_array_for_carrier['shipping_address_1']
        //               $l2 to $new_array_for_carrier['shipping_address_2']
        //               $l3 . ' ' . $l4 to $new_array_for_carrier['shipping_address_3']        
      }
    }
    return $new_array_for_carrier;
  }

  //****** Model filter to force unique customer choice in all shipping packages (multi vendors)
  add_action( 'cdi_filterbool_multipackage_rate', 'example_cdi_filterbool_multipackage_rate', 10, 3 );
  function example_cdi_filterbool_multipackage_rate( $onlyoneshippingtoshow, $rates, $package  ) {
    // Exemple of filter code 'cdi_filterbool_multipackage_rate' 
    // For market-place trought WC Shipping Package. Must be adapted depending on the market place plugins used
    $packages = WC()->shipping->get_packages() ;
    if (count($packages)) {
      $firstpackage = $packages['0'] ; 
      $firstpackageKey = $firstpackage['packageKey'];
      if ($package['packageKey'] !== $firstpackageKey) {
        $onlyoneshippingtoshow = true ;
      }
    }
    return $onlyoneshippingtoshow ;
  }

?>
