<?php

/**
 * This file is part of the CDI - Collect and Deliver Interface plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/****************************************************************************************/
/* CDI Choix de Livraison (Pickup Location / Point de Retrait)                          */
/****************************************************************************************/

class cdi_c_Reference_Livraisons {
	public static function init() {
		if ( get_option( 'cdi_o_settings_methodreferal' ) == 'yes' ) {
			add_action( 'woocommerce_cart_calculate_fees', __CLASS__ . '::cdi_woocommerce_cart_calculate_fees' );
			add_action( 'woocommerce_review_order_after_cart_contents', __CLASS__ . '::cdi_woocommerce_review_order_after_cart_contents' );
			add_filter( 'woocommerce_checkout_posted_data', __CLASS__ . '::cdi_woocommerce_checkout_posted_data' );
			add_action( 'woocommerce_checkout_update_order_meta', __CLASS__ . '::cdi_woocommerce_checkout_update_order_meta', 10, 2 );
			add_action( 'wp_ajax_set_pickuplocation', __CLASS__ . '::cdi_callback_set_pickuplocation' );
			add_action( 'wp_ajax_nopriv_set_pickuplocation', __CLASS__ . '::cdi_callback_set_pickuplocation' );
			add_action( 'wp_ajax_set_pickupgooglemaps', __CLASS__ . '::cdi_callback_show_pickupgooglemaps' );
			add_action( 'wp_ajax_nopriv_set_pickupgooglemaps', __CLASS__ . '::cdi_callback_show_pickupgooglemaps' );
			add_action( 'wp_enqueue_scripts', __CLASS__ . '::cdi_wp_enqueue_scripts', 300 );
			add_action( 'wp_footer', __CLASS__ . '::cdi_wp_footer' );
			add_filter( 'woocommerce_package_rates', __CLASS__ . '::cdi_woocommerce_package_rates', 100, 2 );
			add_filter( 'cdi_filterbool_tobeornottobe_shipping_rate', __CLASS__ . '::cdi_ex_filterbool_tobeornottobe_shipping_rate', 10, 2 );
			
			// Here beginning of special code if Woocommerce Blocks Checkout
			// Everything that follows is to overcome WC blocks bugs or incompatibilities
			add_action( 'wp_ajax_woocommerce_review_order_after_cart_contents', __CLASS__ . '::cdi_callback_woocommerce_review_order_after_cart_contents' );
			add_action( 'wp_ajax_nopriv_woocommerce_review_order_after_cart_contents', __CLASS__ . '::cdi_callback_woocommerce_review_order_after_cart_contents' );
			add_action( 'woocommerce_store_api_checkout_order_processed', __CLASS__ . '::cdi_woocommerce_store_api_checkout_order_processed' );	
		}
	}
	
	// Here beginning of special code if Woocommerce Blocks Checkout
	// Everything that follows is to overcome WC blocks bugs or incompatibilities
	public static function cdi_callback_woocommerce_review_order_after_cart_contents() {
		self::cdi_woocommerce_review_order_after_cart_contents( $cdiajax = 'yes' ) ;
		wp_die();
	}
	public static function cdi_woocommerce_store_api_checkout_order_processed( $order ) {
		$data = array() ;
		$data = self::cdi_woocommerce_checkout_posted_data( $data ) ; 
		$order_id = $order->get_id();
		self::cdi_woocommerce_checkout_update_order_meta( $order_id, $data ) ;
	}

	public static function cdi_woocommerce_package_rates( $rates, $package ) {
		$chosen_shipping = null;
		$arr_chosen_shipping = WC()->session->get( 'chosen_shipping_methods' );
		if ( $arr_chosen_shipping ) {
			foreach ( $arr_chosen_shipping as $key => $chosen_shipping ) { // to keep only the first shipping with numeric key (which is the standard WC selected). Typically its 0
				if ( is_numeric( $key ) ) {
					break;
				}
			}
		}

		$onlyoneshippingtoshow = false; // Default is to show all shippings in others parckages
		if ( isset( $package['recurring_cart_key'] ) ) { // Forced to no show if it is WC subscription package
			$onlyoneshippingtoshow = true;
		}
		$onlyoneshippingtoshow = apply_filters( 'cdi_filterbool_multipackage_rate', $onlyoneshippingtoshow, $rates, $package ); // to be used by others users of multi shipping (market places)
		// examples of filter 'cdi_filterbool_multipackage_rate' to see in examples library

		$newrates = array();
		if ( $onlyoneshippingtoshow ) {
			foreach ( (array) $rates as $rate_id => $rate ) { // Standard is to try to get the rate having the same id in the first package
				if ( $rate_id == $chosen_shipping ) {
					$newrates[ $rate_id ] = apply_filters( 'cdi_filterarray_forcedpackage_rate', $rate, $rates, $package ); // standard can be changed by users
					break;
				}
			}
			if ( count( $newrates ) == 0 ) { // If no rate has been found, the first in $rates is taken
				foreach ( (array) $rates as $rate_id => $rate ) {
					$newrates[ $rate_id ] = apply_filters( 'cdi_filterarray_forcedpackage_rate', $rate, $rates, $package ); // standard can be changed by users
					break;
				}
			}
		} else {
			// To select in package rates only the first exclusive method found
			$arrayexclusivemethodoption = explode( ',', get_option( 'cdi_o_settings_exclusiveshippingmethod' ) );
			$arrayexclusivemethod = array_map( 'trim', $arrayexclusivemethodoption );
			foreach ( (array) $rates as $rate_id => $rate ) {
				$startofid = explode( ':', $rate->id );
				// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $startofid[0] . ' - ' . get_option('cdi_o_settings_exclusiveshippingmethod'), 'msg') ;
				if ( in_array( $startofid[0], $arrayexclusivemethod ) ) { // Is it a racine-name ?
					$newrates[ $rate_id ] = $rate;
					break;
				}
				if ( isset( $startofid[1] ) && is_numeric( $startofid[1] ) ) { // Is it a shipping zone method 2.6 ?
					if ( in_array( $startofid[0] . ':' . $startofid[1], $arrayexclusivemethod ) ) { // So now test if it is racine-name:instance ?
						$newrates[ $rate_id ] = $rate;
						break;
					}
				}
			}
		}
		return ! empty( $newrates ) ? $newrates : $rates;
	}

	public static function cdi_wp_enqueue_scripts() {
		global $woocommerce;
		wp_enqueue_script( 'cdi_handle_js_front', plugin_dir_url( __FILE__ ) . '../js/cdifront.js', array( 'jquery' ), $ver = false, $in_footer = false );
		$linemaxsize = get_option( 'cdi_o_settings_maxsizeaddressline' );
		wp_add_inline_script( 'cdi_handle_js_front', 'var linemaxsize = "' . $linemaxsize . '";', 'before' );
		if ($woocommerce->customer == null) {
			return;
		}
		$country = $woocommerce->customer->get_billing_country();
		if ( $country == 'S1' ) {
			wp_add_inline_script( 'cdi_handle_js_front', 'jQuery(document).ready(function(){ document.getElementById("billing_state_field").style.display = "none"; });', 'before' );
		}
		if ( cdi_c_Function::cdi_is_checkout() ) { // No useful to do this if not the checkout page
			$urlglobeopen = plugins_url( 'images/globeopen.png', dirname( __FILE__ ) );
			$urlglobeclose = plugins_url( 'images/globeclose.png', dirname( __FILE__ ) );
			if ( get_option( 'cdi_o_settings_mapopen' ) == 'yes' ) {
				$htmlurlglobe = $urlglobeclose;
			} else {
				$htmlurlglobe = $urlglobeopen;
			}
			$ajaxurl = admin_url( 'admin-ajax.php' );
			$whereselectorpickup = apply_filters( 'cdi_filterjava_retrait_whereselectorpickup', get_option( 'cdi_o_settings_wheremustbeemap' ) );
			$varjs = 'var cdiurlglobeopen = "' . $urlglobeopen . '" ; ' .
			   'var cdiurlglobeclose = "' . $urlglobeclose . '" ; ' .
			   'var cdiajaxurl = "' . $ajaxurl . '" ; ' .
			   "var cdiwhereselectorpickup = '" . $whereselectorpickup . "' ; ";
			wp_enqueue_script( 'cdi_handle_mapcdi1', plugin_dir_url( __FILE__ ) . '../js/cdimap1.js', array( 'jquery' ), $ver = false, $in_footer = false );
			wp_add_inline_script( 'cdi_handle_mapcdi1', $varjs );
			wp_enqueue_script( 'cdi_handle_mapcdi2', plugin_dir_url( __FILE__ ) . '../js/cdimap2.js', array( 'jquery' ), $ver = false, $in_footer = false );
			if ( 'yes' == get_option( 'cdi_o_settings_selectclickonmap' ) ) {
				wp_enqueue_script( 'cdi_handle_mapcdi3', plugin_dir_url( __FILE__ ) . '../js/cdimap3.js', array( 'jquery' ), $ver = false, $in_footer = false );
			}
			if ( ( get_option( 'cdi_o_settings_mapengine' ) == 'om' ) ) {
				wp_enqueue_style( 'cdi_handle_css_ol', plugin_dir_url( __FILE__ ) . '../css/ol.css' );
				wp_enqueue_script( 'cdi_handle_js_ol', plugin_dir_url( __FILE__ ) . '../js/ol.js' );
				wp_enqueue_script( 'cdi_handle_refmapom', plugin_dir_url( __FILE__ ) . '../js/reference-map-om.js', array( 'jquery' ), $ver = false, $in_footer = true );
				$varjs = WC()->session->get( 'cdi_handle_refmapom' );
				wp_add_inline_script( 'cdi_handle_refmapom', $varjs, $position = 'before' );
			} else {
				$key = get_option( 'cdi_o_settings_googlemapsapikey' );
				if ( $key == null or $key == '' ) { // Google maps API depending if key exists
					wp_enqueue_script( 'cdi_handle_js_googlemaps', 'https://maps.google.com/maps/api/js' );
				} else {
					wp_enqueue_script( 'cdi_handle_js_googlemaps', 'https://maps.googleapis.com/maps/api/js?key=' . $key );
				}
				wp_enqueue_script( 'cdi_handle_refmapgm', plugin_dir_url( __FILE__ ) . '../js/reference-map-gm.js', array( 'jquery' ), $ver = false, $in_footer = true );
				$varjs = WC()->session->get( 'cdi_handle_refmapgm' );
				wp_add_inline_script( 'cdi_handle_refmapgm', $varjs, $position = 'before' );
			}
			wp_enqueue_style( 'cdi_handle_css_front', plugin_dir_url( __FILE__ ) . '../css/cdifront.css' );

		}
	}

	public static function cdi_control_pickup_list( $chosen_shipping ) {
		// Verify if in pickup list
		global $woocommerce;
		$pickuplist = str_replace( ' ', '', get_option( 'cdi_o_settings_pickupmethodnames' ) );
		$arraypickuplist = explode( ',', $pickuplist );
		$arraypickuplist = array_map( 'trim', $arraypickuplist );
		if ($chosen_shipping) {
			$arraychosen = explode( ':', $chosen_shipping ); // explode = method : instance : suffixe
		}else{
			$arraychosen = array () ;
		}
		$inpickup = null;
		$relaytype = null;
		if ( $woocommerce->customer->get_shipping_address() && $woocommerce->customer->get_shipping_postcode() && $woocommerce->customer->get_shipping_city() && $woocommerce->customer->get_shipping_country()  // An address seems exist
		&& $chosen_shipping // and a method exists
		&& isset( $arraychosen[1] ) && is_numeric( $arraychosen[1] ) ) { // and is a shipping zone method 2.6
			// Test if in the pickup list and extract filterrelay
			$return = null;
			$carrier = WC()->session->get( 'cdi_carrier' );
			$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
			$route = 'cdi_c_Carrier_' . $carrier . '::cdi_isit_pickup_authorized';
			$return = ( $route )();
			if ( $return ) { // and is in the pickup country list of the carrier
				foreach ( $arraypickuplist as $x ) {
					$arrx = explode( '=', $x );
					$arry = explode( ':', $arrx[0] );
					if ( ! isset( $arry[1] ) or ! is_numeric( $arry[1] ) ) {
						if ( $arry[0] == $arraychosen[0] ) { // Default without instance num
							if ( isset( $arrx[1] ) ) {
								$relaytype = $arrx[1];
							} else {
								$relaytype = '1'; // Default without the "=0 or 1"
							}
							$inpickup = 1;
							break;
						}
					} else {
						if ( $arry[0] . ':' . $arry[1] == $arraychosen[0] . ':' . $arraychosen[1] ) { // Ok with instance num
							if ( isset( $arrx[1] ) ) {
								$relaytype = $arrx[1];
							} else {
								  $relaytype = '1'; // Default without the "=0 or 1"
							}
							$inpickup = 1;
							break;
						}
					}
				}
			}
		}
		return array( $inpickup, $relaytype );
	}

	public static function cdi_get_shipping_and_product() {
		global $woocommerce;
		// We must consider only one package (or whole cart) for CDI process in WC orders and gateway. The default is cart. But this can be change with a filter
		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' ); // Warning, may not have been updated by WC
		$shiptype_option = get_option( 'cdi_o_settings_shippingpackageincart' );
		if ( $shiptype_option == 'first' ) {
			$rank_method = 0;
		} elseif ( $shiptype_option == 'last' ) {
			$rank_method = count( $chosen_methods ) - 1;
		} else {
			$rank_method = -1; // cart
		}
		$rank_method = apply_filters( 'cdi_filterstring_chosen_shipping', $rank_method, $chosen_methods );
		// Set array of shipping packages => id product to pass to WC process. WC dont do that !
		// Search chosen_shipping to consider
		$packages = WC()->shipping->get_packages();
		if ( count( $packages ) == 0 ) {
			return; // Seems to be a WC bug (erroneous call without packages returned when in the before checkout filter)
		}
		$chosen_shipping = '';
		$chosen_products = array();
		if ( $rank_method !== -1 ) {
			$i = 0;
			foreach ( $packages as $package ) {
				foreach ( $package['contents'] as $item ) {
					$chosen_products[] = $item['product_id'];
				}
				if ( $i == $rank_method ) {
					break;
				}
				$i = $i + 1;
			}
			$i = 0;
			foreach ( $chosen_methods as $chosen_method ) {
				$chosen_shipping = $chosen_method; // $chosen_shipping = method : instance : suffixe
				if ( $i == $rank_method ) {
					break;
				}
				$i = $i + 1;
			}
		} else { // Case of whole cart for CDI
			foreach ( $packages as $package ) {
				foreach ( $package['contents'] as $item ) {
					$chosen_products[] = $item['product_id'];
				}
			}
			foreach ( $chosen_methods as $chosen_method ) {
				$chosen_shipping = $chosen_method; // $chosen_shipping = method : instance : suffixe - Only the first chosen_method is selected (named Expedition)
				break;
			}
		}
		// search shipping method label
		$shipping_method_name = '';
		$needs_shipping = WC()->cart->needs_shipping();
		if ( $needs_shipping ) { // Because WC()->session->get( 'chosen_shipping_methods' ) don't work for all cases
			foreach ( $packages as $package ) {
				foreach ( $package['rates'] as $rate_id => $shipping_rate ) {
					if ( $rate_id == $chosen_shipping ) {
						$shipping_method_name = $shipping_rate->label;
						break;
					}
				}
			}
		} else {
			$chosen_shipping = '';
			$shipping_method_name = '';
		}
		WC()->session->set( 'cdi_refshippingmethod', $chosen_shipping );
		WC()->session->set( 'cdi_chosen_products', $chosen_products );
		WC()->session->set( 'cdi_shipping_method_name', $shipping_method_name );
		$carrier = self::get_string_between( $chosen_shipping, 'cdi_shipping_', '_' );
		if ( ! isset( $carrier ) or $carrier == '' ) {
			$carrier = 'notcdi';
			// force carrier for Non CDI method which must be forced with a carrier
			$arraychosen = explode( ':', $chosen_shipping ); // explode = method : instance : suffixe
			$forcednocdishipping = get_option( 'cdi_o_settings_forcednocdishipping' );
			$arrayforcednocdishipping = explode( ',', $forcednocdishipping );
			$arrayforcednocdishipping = array_map( 'trim', $arrayforcednocdishipping );
			foreach ( $arrayforcednocdishipping as $relation ) {
				$arrayrelation = explode( '=', $relation );
				if ( isset( $arraychosen[1] ) ) { // test case for legacy shipping method non WC 2.6
					$arraychosenun = $arraychosen[0] . ':' . $arraychosen[1];
				} else {
					$arraychosenun = $arraychosen[0];
				}
				if ( $arrayrelation[0] && ( ( $arrayrelation[0] == '*' ) or ( $arrayrelation[0] == $arraychosen[0] ) or ( $arrayrelation[0] == $arraychosenun ) ) ) {
					$carrier = trim( $arrayrelation[1] );
				}
			}
		}
		WC()->session->set( 'cdi_carrier', $carrier );
	}

	public static function cdi_woocommerce_cart_calculate_fees( $cart ) {
		// Activate when calculate_fees
		global $woocommerce;
		global $msgtofrontend;
		if ( cdi_c_Function::cdi_is_checkout() ) { // No useful to do this if not the checkout page AND only if Ajax		
			// Suppress of Ajax condition starting from CDI 3.7.8 (no more rebound)
			if ( ! empty( $cart->recurring_cart_key ) ) {
				return; // Case of WC subscription plugin processing
			}
			// Initial shipping settings for all shipping method
			$needs_shipping = WC()->cart->needs_shipping();
			if ( $needs_shipping ) {
				self::cdi_get_shipping_and_product();
				$chosen_shipping = WC()->session->get( 'cdi_refshippingmethod' );
				$chosen_products = (array) WC()->session->get( 'cdi_chosen_products' );
				$shipping_method_name = WC()->session->get( 'cdi_shipping_method_name' );
				$carrier = WC()->session->get( 'cdi_carrier' );
			} else {
				$chosen_shipping = null;
				WC()->session->set( 'cdi_refshippingmethod', $chosen_shipping );
				$chosen_products = array() ;
				WC()->session->set( 'cdi_chosen_products', $chosen_products );
				$shipping_method_name = null;
				WC()->session->set( 'cdi_shipping_method_name', $shipping_method_name );
				$carrier = null;
				WC()->session->set( 'cdi_carrier', $carrier );
			}
			// Verify if nothing has been done in last 300s (to avoid multiple calls)
			$tokentimereplay = time();
			$oldtokentimereplay = WC()->session->get( 'cdi_tokentimereplay' );
			if ( ! $oldtokentimereplay or ( ( $oldtokentimereplay + 300 ) < $tokentimereplay ) ) {
				$tokentimereplaypass = 1;
			} else {
				$tokentimereplaypass = 0;
			}
			WC()->session->set( 'cdi_tokentimereplay', $tokentimereplay );
			// Verify if a change in shipping method or shipping data or if nothing has been done from a long time
			$unikkeydisplpickup = $chosen_shipping . '-' . $woocommerce->customer->get_shipping_country() . '-' . $woocommerce->customer->get_shipping_city() . '-' . $woocommerce->customer->get_shipping_postcode() . '-' . $woocommerce->customer->get_shipping_address();
			$lastunikkeydisplpickup = WC()->session->get( 'cdi_unikkeydisplpickup' );
			if ( ! isset( $lastunikkeydisplpickup ) or $lastunikkeydisplpickup == '' or $lastunikkeydisplpickup !== $unikkeydisplpickup or $tokentimereplaypass == 1 ) {
				WC()->session->set( 'cdi_forcedproductcode', '' );
				WC()->session->set( 'cdi_pickuplocationid', '' );
				WC()->session->set( 'cdi_pickuplocationlabel', '' );
				WC()->session->set( 'cdi_pickupfulladdress', '' );
				WC()->session->set( 'cdi_return_liste_points_livraison', '' );
				WC()->session->set( 'cdi_unikkeydisplpickup', $unikkeydisplpickup );
			} else {
				return;
			}
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'User:' .  get_current_user_id() . ' Ship:' . $chosen_shipping . ' Prod:' . implode(',', $chosen_products), 'msg' );		
			if ($chosen_shipping) {
				$arraychosen = explode( ':', $chosen_shipping ); // explode = method : instance : suffixe
			}else{
				$arraychosen = array() ;
			}
			$testpickup = self::cdi_control_pickup_list( $chosen_shipping );
			$inpickup = $testpickup['0'];
			$relaytype = $testpickup['1'];
			if ( $inpickup == 1 ) {  // We are in the pickup list
				$cdi_return_liste_points_livraison = self::cdi_get_points_livraison( $relaytype );
				if ( $cdi_return_liste_points_livraison == false ) { // Check if pickup addresses exist ?!
					// it seems as if an error. The address seems invalid
					wc_add_notice( __( 'The carrier can not send its relay addresses. Please try again.', 'cdi' ) . $msgtofrontend, $notice_type = 'error' );
				} else {
					WC()->session->set( 'cdi_return_liste_points_livraison', $cdi_return_liste_points_livraison );
				}
			}

			// test if in the forced product code list
			$forcedproductcode = get_option( 'cdi_o_settings_forcedproductcodes' );
			$arrayforcedproductcode = explode( ',', $forcedproductcode );
			$arrayforcedproductcode = array_map( 'trim', $arrayforcedproductcode );
			$codeproductfound = '';
			foreach ( $arrayforcedproductcode as $relation ) {
				$arrayrelation = explode( '=', $relation );
				if ( isset( $arraychosen[1] ) ) { // test case for legacy shipping method non WC 2.6
					$arraychosenun = $arraychosen[0] . ':' . $arraychosen[1];
				} else {
					if ( isset( $arraychosen[0] ) ) {
						$arraychosenun = $arraychosen[0];
					}else{
						$arraychosenun = null;
					}				
				}
				if ( $arrayrelation[0] && $arraychosenun && ( ( $arrayrelation[0] == $arraychosen[0] ) or ( $arrayrelation[0] == $arraychosenun ) ) ) {
					$codeproductfound = $arrayrelation[1];
				}
			}
			WC()->session->set( 'cdi_forcedproductcode', $codeproductfound );
		} //End if checkout
	}

	public static function cdi_test_carrier() {
		$carrier = WC()->session->get( 'cdi_carrier' );
		$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_test_carrier';
		$return = ( $route )();
		return $return;
	}

	public static function cdi_get_points_livraison( $relaytype ) {
		$carrier = WC()->session->get( 'cdi_carrier' );
		$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_get_points_livraison';
		$return = ( $route )( $relaytype );
		return $return;
	}

	public static function cdi_woocommerce_review_order_after_cart_contents($ajaxcdi = 'no') {
		// When choice shipping done, display the pickup box
		global $woocommerce;
		if ( true /*is_ajax()*/ ) {
			$token = time(); // To view only the newest token div in js
			$cdi_return_liste_points_livraison = WC()->session->get( 'cdi_return_liste_points_livraison' );
			$resultmap = array(
				'structure' => '',
				'script' => '',
			);
			if ( $cdi_return_liste_points_livraison ) {
				$listePointRetraitAcheminement = $cdi_return_liste_points_livraison->return->listePointRetraitAcheminement;
				$arrayabstract = array();
				$nbpointretrait = 0;
				foreach ( $listePointRetraitAcheminement as $PointRetrait ) {
					if ( $PointRetrait->reseau !== 'X00' && $nbpointretrait < 30 ) { // Exclude X00 networks
						$nbpointretrait = $nbpointretrait + 1;
						$arrayabstract[] = esc_attr(
							cdi_c_Function::cdi_sanitize_voie( $PointRetrait->nom ) . ' =&gt; ' .
							   $PointRetrait->adresse1 . ' ' .
							   $PointRetrait->adresse2 . ' ' .
							   $PointRetrait->codePostal . ' ' .
							   $PointRetrait->localite . ' =&gt; Distance: ' .
							   $PointRetrait->distanceEnMetre . 'm =&gt; Id: ' .
							$PointRetrait->identifiant
						);

					}
				}
				if ( get_option( 'cdi_o_settings_mapopen' ) == 'yes' ) {
					$resultmap = self::cdi_calculate_js_map();
					$htmlurlglobe = plugins_url( 'images/globeclose.png', dirname( __FILE__ ) );
				} else {
					$htmlurlglobe = plugins_url( 'images/globeopen.png', dirname( __FILE__ ) );
				}
				$insertmsg = '';
				$insertmsg .= '<div id="popupmap" style="width:100%;">' . $resultmap['structure'] . '</div>'; // Place reserved for the popup maps
				$insertmsg = $insertmsg . '<div id="zoneiconmap" style="width:100%;">';
				$insertmsg = $insertmsg . '<span>' . __( 'Select your pickup locations :', 'cdi' ) . '</span>';
				$insertmsg = $insertmsg . '<span id="iconpopupmap" style="width:100%;">';
				$insertmsg = $insertmsg . '<div id="cdiclickearth" title="Show/Hide map" style="float:right;"> ';
				$insertmsg = $insertmsg . '<input type="image" id="pickupgooglemaps" name="pickupgooglemaps" value="pickupgooglemaps" src="' . esc_url( $htmlurlglobe ) . '"> ';
				$insertmsg = $insertmsg . '</div></span>';
				$insertselect = '<div style="width:100%; overflow:hidden"><select id="pickupselect" name="pickupselect" style="width:100%; overflow:hidden;">' . '<option value="">' . __( 'Choose a location', 'cdi' ) . '</option>';
				foreach ( $arrayabstract as $abstract ) {
					$idpt = stristr( $abstract, ' Id: ' );
					$idpt = str_replace( ' Id: ', '', $idpt );
					$insertselect = $insertselect . '<option style="overflow:hidden;" value="' . esc_attr( $idpt ) . '">' . esc_attr( $abstract ) . '</option>';
				}
				$insertselect = $insertselect . '</select></div></div>';
				$insertmsg = $insertmsg . apply_filters( 'cdi_filterhtml_retrait_selectoptions', $insertselect, $listePointRetraitAcheminement );
			} else {
				$insertmsg = '';
			}
			$return = '<div id="' . esc_attr( $token ) . '" class="cdiselectlocation"> ' . wp_kses_post( $insertmsg ) . ' <script>' .  esc_js( $resultmap['script'] ) . '</script></div>' ;
				
			if ($ajaxcdi == 'yes') {
				// Everything that follows is to overcome WC blocks bugs
				echo wp_json_encode( $return) ;
			}else{
				echo $return ;
			}
		}
	}

	public static function get_string_between( $string, $start, $end ) {	
		$string = ' ' . $string;
		$ini = strpos( $string, $start );
		if ( $ini == 0 ) {
			return '';
		}
		$ini += strlen( $start );
		if ( $end !== null ) {
			$len = strpos( $string, $end, $ini ) - $ini;
			return substr( $string, $ini, $len );
		} else {
			return substr( $string, $ini );
		}
	}

	public static function cdi_html_retrait_descpickup( $PointRetrait ) {
		$return = '<div id="selretrait" data-value="' . esc_attr( $PointRetrait->identifiant ) . '" class="cdiselretrait' . esc_attr( $PointRetrait->identifiant ) . '">';
		$return .= '<p style="width:100%; display:inline-block;"><em>(' . esc_attr( $PointRetrait->identifiant ) . ')</em><a class="selretrait button" style="float: right; padding:5px; margin:0; border:3px solid red; border-radius: 15px; color:white; background-color:black ;" id="selretraitshown" >Sélectionner</a></p>';
		$return .= '<div id="selretraithidden" style="display:none;"><p style="text-align:center;"><a class="button">Point Retrait sélectionné</a></p></div>';
		$return .= '<p style="margin:0px; font-size:15px; font-weight:1000;"><mark>' . cdi_c_Function::cdi_sanitize_voie( $PointRetrait->nom ) . '</mark></p>';
		$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"><mark>' . addslashes( esc_attr( $PointRetrait->adresse1 . ' ' . $PointRetrait->adresse2 ) ) . '</mark></p>';
		$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"><mark>' . addslashes( esc_attr( $PointRetrait->codePostal . ' ' . $PointRetrait->localite ) ) . '</mark></p>';
		if ( $PointRetrait->indiceDeLocalisation ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"><mark>' . addslashes( esc_attr( $PointRetrait->indiceDeLocalisation ) ) . '</mark></p>';
		}
		$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:30px;"><em>Distance: ' . esc_attr( $PointRetrait->distanceEnMetre ) . 'm</em></p>';

		if ( isset( $PointRetrait->StandardHoursOfOperation ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"> Horaire général : ' . esc_attr( $PointRetrait->StandardHoursOfOperation ) . '</p>';
		}
		if ( isset( $PointRetrait->horairesOuvertureLundi ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"> Lundi ' . esc_attr( $PointRetrait->horairesOuvertureLundi ) . '</p>';
		}
		if ( isset( $PointRetrait->horairesOuvertureMardi ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"> Mardi ' . esc_attr( $PointRetrait->horairesOuvertureMardi ) . '</p>';
		}
		if ( isset( $PointRetrait->horairesOuvertureMercredi ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"> Mercredi ' . esc_attr( $PointRetrait->horairesOuvertureMercredi ) . '</p>';
		}
		if ( isset( $PointRetrait->horairesOuvertureJeudi ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"> Jeudi ' . esc_attr( $PointRetrait->horairesOuvertureJeudi ) . '</p>';
		}
		if ( isset( $PointRetrait->horairesOuvertureVendredi ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"> Vendredi ' . esc_attr( $PointRetrait->horairesOuvertureVendredi ) . '</p>';
		}
		if ( isset( $PointRetrait->horairesOuvertureSamedi ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"> Samedi ' . esc_attr( $PointRetrait->horairesOuvertureSamedi ) . '</p>';
		}
		if ( isset( $PointRetrait->horairesOuvertureDimanche ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;"> Dimanche ' . esc_attr( $PointRetrait->horairesOuvertureDimanche ) . '</p>';
		}
		$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:30px;"><em>GPS: ' . esc_attr( $PointRetrait->coordGeolocalisationLatitude . ' ' . $PointRetrait->coordGeolocalisationLongitude ) . '</em></p>';
		if ( isset( $PointRetrait->parking ) and ($PointRetrait->parking !== 'false') ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;">Parking: ' . esc_attr( str_replace('true', 'oui', $PointRetrait->parking ) ) . '</p>';
		}
		if ( isset( $PointRetrait->accesPersonneMobiliteReduite ) and ($PointRetrait->accesPersonneMobiliteReduite !== 'false') ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;">Mobilité réduite: ' . esc_attr( str_replace('true', 'oui', $PointRetrait->accesPersonneMobiliteReduite ) ) . '</p>';
		}
		if ( isset( $PointRetrait->langue ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;">Langue: ' . esc_attr( $PointRetrait->langue ) . '</p>';
		}
		if ( isset( $PointRetrait->poidsMaxi ) ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;">Poids maxi: ' . esc_attr( $PointRetrait->poidsMaxi ) . '</p>';
		}
		if ( isset( $PointRetrait->loanOfHandlingTool ) and ($PointRetrait->loanOfHandlingTool !== 'false') ) {
			$return .= '<p style="margin:0px; font-size:15px; font-weight:1000; line-height:18px;">Equipements de manipulation: ' . esc_attr( str_replace('true', 'oui', $PointRetrait->loanOfHandlingTool ) ) . '</p>';
		}
		$return .= '</div>';
		return $return;
	}

	public static function cdi_calculate_js_map() {
		global $woocommerce;
		$errresultmap = array(
			'structure' => '',
			'script' => '',
		);			
		$carrier = WC()->session->get( 'cdi_carrier' );
		$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
		$urliconmarker = apply_filters( 'cdi_filterurl_retrait_iconmarker', plugins_url( 'images/iconpick' . $carrier . '.png', dirname( __FILE__ ) ) );
		$urliconmarkerselect = apply_filters( 'cdi_filterurl_retrait_iconmarkerselect', plugins_url( 'images/iconselect' . $carrier . '.png', dirname( __FILE__ ) ) );
		$urliconcustomer = apply_filters( 'cdi_filterurl_retrait_iconcustomer', plugins_url( 'images/iconcustomer.png', dirname( __FILE__ ) ) );
		$cdi_return_liste_points_livraison = WC()->session->get( 'cdi_return_liste_points_livraison' );
		if ( ! is_object( $cdi_return_liste_points_livraison ) or ! property_exists( $cdi_return_liste_points_livraison, 'return' ) ) {
			wc_add_notice( __( 'CDI can not geolocate this address. Please try again.', 'cdi' ) . ' (Pickup list internal error)', $notice_type = 'error' );	
			return $errresultmap ; // Error in $cdi_return_liste_points_livraison
		}
		$listePointRetraitAcheminement = $cdi_return_liste_points_livraison->return->listePointRetraitAcheminement;
		$listmarks = array();
		$nbpointretrait = 0;
		$latfallback = 0;
		$lonfallback = 0;
		// For automatic map zoom
		$zoomlatmin = +90;
		$zoomlatmax = -90;
		$zoomlonmin = +180;
		$zoomlonmax = -180;
		$ecardegrés = 0;
		foreach ( $listePointRetraitAcheminement as $PointRetrait ) {
			if ( $PointRetrait->reseau !== 'X00' && $nbpointretrait < 30 ) { // Exclude X00 networks
				$nbpointretrait = $nbpointretrait + 1;
				$urlicon = $urliconmarker;
				$pickuplocationid = WC()->session->get( 'cdi_pickuplocationid' );
				if ( $pickuplocationid !== null and $pickuplocationid !== '' and $pickuplocationid == $PointRetrait->identifiant ) {
					$urlicon = $urliconmarkerselect;
				}
				$viewselected = cdi_c_Function::cdi_sanitize_voie( $PointRetrait->nom ) . ' =&gt; ' .
						 cdi_c_Function::cdi_sanitize_voie( $PointRetrait->adresse1 ) . ' ' .
						 cdi_c_Function::cdi_sanitize_voie( $PointRetrait->adresse2 ) . ' ' .
						 $PointRetrait->codePostal . ' ' .
						 cdi_c_Function::cdi_sanitize_voie( $PointRetrait->localite ) . ' =&gt; Distance: ' .
						 $PointRetrait->distanceEnMetre . 'm =&gt; Id: ' .
						 $PointRetrait->identifiant;
				if ( 'yes' == get_option( 'cdi_o_settings_selectclickonmap' ) ) {
					$viewselected = self::cdi_html_retrait_descpickup( $PointRetrait );
				}
				$listmarks[] = array(
					'lati' => $PointRetrait->coordGeolocalisationLatitude,
					'long' => $PointRetrait->coordGeolocalisationLongitude,
					'desc' => apply_filters( 'cdi_filterhtml_retrait_descpickup', $viewselected, $PointRetrait ),
					'icon' => $urlicon,
				);
				$latfallback = $latfallback + $PointRetrait->coordGeolocalisationLatitude;
				$lonfallback = $lonfallback + $PointRetrait->coordGeolocalisationLongitude;
				// For automatic map zoom
				if ( $zoomlatmin > $PointRetrait->coordGeolocalisationLatitude ) {
					$zoomlatmin = $PointRetrait->coordGeolocalisationLatitude;
				}
				if ( $zoomlatmax < $PointRetrait->coordGeolocalisationLatitude ) {
					$zoomlatmax = $PointRetrait->coordGeolocalisationLatitude;
				}
				if ( $zoomlonmin > $PointRetrait->coordGeolocalisationLongitude ) {
					$zoomlonmin = $PointRetrait->coordGeolocalisationLongitude;
				}
				if ( $zoomlonmax < $PointRetrait->coordGeolocalisationLongitude ) {
					$zoomlonmax = $PointRetrait->coordGeolocalisationLongitude;
				}
			}
		}
		if ( $nbpointretrait != 0 ) {
			$latfallback = $latfallback / $nbpointretrait;
			$lonfallback = $lonfallback / $nbpointretrait;
		} else {
			$latfallback = 0;
			$lonfallback = 0;
		}
		if ( ( get_option( 'cdi_o_settings_mapengine' ) == 'om' ) ) {
			// Calc geolocate of customer
			$arraygps = cdi_c_Function::cdi_geolocate_customer();
			if ( ! $arraygps ) {
				wc_add_notice( __( 'Open Maps can not geolocate this address. Please try again.', 'cdi' ) . ' (Customer geolocate error)', $notice_type = 'error' );			
				return $errresultmap;
			}
			$lat = $arraygps['lat'];
			$lon = $arraygps['lon'];
			$addresscustomer = $arraygps['addresscustomer'];

			if ( ! $lat or ! $lon ) { // Process fallback or error
				$lat = $latfallback;
				$lon = $lonfallback;
				$urliconcustomer = plugins_url( 'images/iconcustomerfallback.png', dirname( __FILE__ ) );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Customer address fallback.', 'tec' );
				// wc_add_notice( __( 'Open Map can not geolocate this address. Please try again.','cdi' ) . ' (' . $address . ')', $notice_type = 'error' );
				// return $errresultmap ;
			}
			// Add marker for customer location
			$listmarks[] = array(
				'lati' => $lat,
				'long' => $lon,
				'desc' => apply_filters( 'cdi_filterhtml_retrait_desccustomer', $addresscustomer, $woocommerce->customer ),
				'icon' => $urliconcustomer,
			);
			// For automatic map zoom
			if ( $zoomlatmin > $lat ) {
				$zoomlatmin = $lat;
			}
			if ( $zoomlatmax < $lat ) {
				$zoomlatmax = $lat;
			}
			if ( $zoomlonmin > $lon ) {
				$zoomlonmin = $lon;
			}
			if ( $zoomlonmax < $lon ) {
				$zoomlonmax = $lon;
			}
			$ecartlat = abs( $zoomlatmax - $zoomlatmin ) * 1.5;
			$ecartlon = abs( $zoomlonmax - $zoomlonmin ) * 1.5;
			$mediumlat = ( $zoomlatmax + $zoomlatmin ) / 2;
			$mediumlon = ( $zoomlonmax + $zoomlonmin ) / 2;
			if ( $ecartlat > $ecartlon ) {
				$ecardegrés = $ecartlat;
			} else {
				$ecardegrés = $ecartlon;
			}
			$scaleom = array(
				'20' => 0.0006,
				'19' => 0.00125,
				'18' => 0.0025,
				'17' => 0.005,
				'16' => 0.01,
				'15' => 0.02,
				'14' => 0.04,
				'13' => 0.08,
				'12' => 0.16,
				'11' => 0.32,
				'10' => 0.64,
				'9' => 1.28,
				'8' => 2.56,
				'7' => 5.12,
				'6' => 10.24,
				'5' => 20.48,
				'4' => 40.96,
				'3' => 81.92,
				'2' => 163.64,
				'1' => 180,
			);
			$zoom = null;
			foreach ( $scaleom as $key => $zoomom ) {
				if ( $ecardegrés < $zoomom ) {
					$zoom = $key;
					break;
				}
			}
			$parammap = apply_filters(
				'cdi_filterarray_retrait_mapparam',
				array(
					'z' => $zoom,
					'w' => '100%',
					'h' => '400px',
					'maptype' => 'ROADMAP',
					'styles' => '[]',
					'style' => 'border:1px solid gray; margin: 0 auto; position:relative; overflow:auto; ',
				)
			);
			// 'maptype' and 'styles' not used with OM
			$parammap = array_merge(
				array(
					'id' => 'cdimapcontainer',
					'lat' => $mediumlat,
					'lon' => $mediumlon,
				),
				$parammap
			);
			if ( is_numeric( $parammap['w'] ) ) {
				$parammap['w'] = $parammap['w'] . 'px';
			}
			if ( is_numeric( $parammap['h'] ) ) {
				$parammap['h'] = $parammap['h'] . 'px';
			}
			$listsites = '[';
			foreach ( $listmarks as $mark ) {
				$listsites .= '[' . esc_attr( $mark['lati'] ) . ',' . esc_attr( $mark['long'] ) . ',\'' . $mark['desc'] . '\',\'' . esc_url( $mark['icon'] ) . '\'],';
			}
			$listsites = substr( $listsites, 0, strlen( $listsites ) - 1 );
			$listsites .= ']';

			$varjs = 'var cdilistsites = ' . $listsites . ' ; ' .
				'var cdiparammaplon = ' . esc_attr( $parammap['lon'] ) . ' ; ' .
				'var cdiparammaplat = ' . esc_attr( $parammap['lat'] ) . ' ; ' .
				'var cdiparammapz = ' . esc_attr( $parammap['z'] ) . ' ; ';
			$cdimaphtml = ' <div id="' . esc_attr( $parammap['id'] ) . '" style="width:' . esc_attr( $parammap['w'] ) . ';height:' . esc_attr( $parammap['h'] ) . ';' . $parammap['style'] . ' "><div id="mapom" style="height:100%; width:100%; position:absolute; top:0px; left:0px;"></div></div><br/>';
			WC()->session->set( 'cdi_handle_refmapom', $varjs );
			// js VARs cannot be update with a wp_add_inline_script following a wp_enqueue_script, so the only solution is an real inline script
			$thejs = file_get_contents( plugin_dir_path( __FILE__ ) . '../js/reference-map-om.js' );
			$resultmap = array();
			$resultmap['structure'] = $cdimaphtml;
			$resultmap['script'] = $varjs . $thejs;
		} else {
			// Calc geolocate of customer
			$addresscustomer = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_string( cdi_c_Function::cdi_sanitize_accents( $woocommerce->customer->get_shipping_address() ) ) ) . ', '
			  . $woocommerce->customer->get_shipping_postcode() . ' '
			  . cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_string( cdi_c_Function::cdi_sanitize_accents( $woocommerce->customer->get_shipping_city() ) ) ) . ', '
			  . $woocommerce->customer->get_shipping_country();
			$address = preg_replace( '/\s+/', ' ', $addresscustomer );
			$address = str_replace( ' ', '+', $address );
			$key = get_option( 'cdi_o_settings_googlemapsapikey' );
			if ( $key == null or $key == '' ) { // Google maps API depending if key exists
				$result = cdi_c_Function::cdi_url_post_remote( 'https://maps.googleapis.com/maps/api/geocode/xml?address=' . $address );
			} else {
				$result = cdi_c_Function::cdi_url_post_remote( 'https://maps.googleapis.com/maps/api/geocode/xml?address=' . $address . '&key=' . $key );
			}
			$status = self::get_string_between( $result, '<status>', '</status>' );
			if ( $status !== 'OK' ) {
				wc_add_notice( __( 'Google Maps can not geolocate this address. Please try again.', 'cdi' ) . ' (' . $status . ')', $notice_type = 'error' );
				return $errresultmap;
			}
			// Extract lat and lon
			$latlng = self::get_string_between( $result, '<location>', '</location>' );
			$lat = self::get_string_between( $latlng, '<lat>', '</lat>' );
			$lon = self::get_string_between( $latlng, '<lng>', '</lng>' );
			// Add marker for customer location
			$listmarks[] = array(
				'lati' => $lat,
				'long' => $lon,
				'desc' => apply_filters( 'cdi_filterhtml_retrait_desccustomer', $addresscustomer, $woocommerce->customer ),  // Last argument customer is now an objet
				'icon' => $urliconcustomer,
			);
			// For automatic map zoom
			if ( $zoomlatmin > $lat ) {
				$zoomlatmin = $lat;
			}
			if ( $zoomlatmax < $lat ) {
				$zoomlatmax = $lat;
			}
			if ( $zoomlonmin > $lon ) {
				$zoomlonmin = $lon;
			}
			if ( $zoomlonmax < $lon ) {
				$zoomlonmax = $lon;
			}
			$ecartlat = abs( $zoomlatmax - $zoomlatmin ) * 1.5;
			$ecartlon = abs( $zoomlonmax - $zoomlonmin ) * 1.5;
			$mediumlat = ( $zoomlatmax + $zoomlatmin ) / 2;
			$mediumlon = ( $zoomlonmax + $zoomlonmin ) / 2;
			if ( $ecartlat > $ecartlon ) {
				$ecardegrés = $ecartlat;
			} else {
				$ecardegrés = $ecartlon;
			}
			$scalegm = array(
				'20' => 0.0006,
				'19' => 0.00125,
				'18' => 0.0025,
				'17' => 0.005,
				'16' => 0.01,
				'15' => 0.02,
				'14' => 0.04,
				'13' => 0.08,
				'12' => 0.16,
				'11' => 0.32,
				'10' => 0.64,
				'9' => 1.28,
				'8' => 2.56,
				'7' => 5.12,
				'6' => 10.24,
				'5' => 20.48,
				'4' => 40.96,
				'3' => 81.92,
				'2' => 163.64,
				'1' => 180,
			);
			$zoom = null;
			foreach ( $scalegm as $key => $zoomgm ) {
				if ( $ecardegrés < $zoomgm ) {
					$zoom = $key;
					break;
				}
			}
			$paramgooglemapcss = apply_filters(
				'cdi_filterarray_retrait_mapparam',
				array(
					'z' => $zoom,
					'w' => '100%',
					'h' => '400px',
					'maptype' => 'ROADMAP',
					'styles' => '[]',
					'style' => 'border:1px solid gray; margin: 0 auto;',
				)
			);
			$paramgooglemap = array_merge(
				array(
					'id' => 'cdimapcontainer',
					'lat' => $mediumlat,
					'lon' => $mediumlon,
				),
				$paramgooglemapcss
			);
			if ( is_numeric( $paramgooglemap['w'] ) ) {
				$paramgooglemap['w'] = $paramgooglemap['w'] . 'px';
			}
			if ( is_numeric( $paramgooglemap['h'] ) ) {
				$paramgooglemap['h'] = $paramgooglemap['h'] . 'px';
			}
			$listsites = '[';
			foreach ( $listmarks as $mark ) {
				$listsites .= '[' . esc_attr( $mark['lati'] ) . ',' . esc_attr( $mark['long'] ) . ',\'' . $mark['desc'] . '\',\'' . esc_url( $mark['icon'] ) . '\'],';
			}
			$listsites = substr( $listsites, 0, strlen( $listsites ) - 1 );
			$listsites .= ']';
			$varjs = 'var cdilistsites = ' . $listsites . ' ; ' .
				'var cdiparamgooglemaplat = ' . esc_attr( $paramgooglemap['lat'] ) . ' ; ' .
				'var cdiparamgooglemaplon = ' . esc_attr( $paramgooglemap['lon'] ) . ' ; ' .
				'var cdiparamgooglemapz = ' . esc_attr( $paramgooglemap['z'] ) . ' ; ' .
				"var cdiparamgooglemapmaptype = '" . esc_attr( $paramgooglemap['maptype'] ) . "' ; " .
				'var cdiparamgooglemapstyles = ' . $paramgooglemap['styles'] . ' ; ' .
				"var cdiparamgooglemapid = '" . esc_attr( $paramgooglemap['id'] ) . "' ; ";
			$cdimaphtml = ' <div id="' . esc_attr( $paramgooglemap['id'] ) . '" style="width:' . esc_attr( $paramgooglemap['w'] ) . ';height:' . esc_attr( $paramgooglemap['h'] ) . ';' . $paramgooglemap['style'] . ' "></div><br /> ';
			WC()->session->set( 'cdi_handle_refmapgm', $varjs );
			// js VARs cannot be update with a wp_add_inline_script following a wp_enqueue_script, so the only solution is an real inline script
			$thejs = file_get_contents( plugin_dir_path( __FILE__ ) . '../js/reference-map-gm.js' );
			$resultmap = array();
			$resultmap['structure'] = $cdimaphtml;
			$resultmap['script'] = $varjs . $thejs;
		}
		return $resultmap;
	}

	public static function cdi_callback_show_pickupgooglemaps() {
		// callback for show the pickup locations on google map
		global $woocommerce;
		$resultmap = self::cdi_calculate_js_map();
		if ( ! $resultmap ) {
			$resultmap = array(
				'structure' => '',
				'script' => '',
			);
		}
		echo wp_kses_post( $resultmap['structure'] ) . '<script>' . esc_js( $resultmap['script'] ) . '</script>';
		wp_die();
	}

	public static function cdi_callback_set_pickuplocation() {
		// callback for storage of pickupselect and display of full info
		global $woocommerce;
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['postpickupselect'] ) ) {
			$pickupchosen = sanitize_text_field( $_POST['postpickupselect'] );
			WC()->session->set( 'cdi_pickuplocationid', $pickupchosen );
			// *********
			$cdi_return_liste_points_livraison = WC()->session->get( 'cdi_return_liste_points_livraison' );
			$pickupdetail = '';
			$eol = "\x0a";
			if ( $cdi_return_liste_points_livraison ) {
				$listePointRetraitAcheminement = $cdi_return_liste_points_livraison->return->listePointRetraitAcheminement;
				$nbpointretrait = 0;
				foreach ( $listePointRetraitAcheminement as $PointRetrait ) {
					if ( $PointRetrait->reseau !== 'X00' && $nbpointretrait < 30 && $PointRetrait->identifiant == $pickupchosen ) {
						$nbpointretrait = $nbpointretrait + 1;	
						WC()->session->set(
							'cdi_pickuplocationlabel',
							cdi_c_Function::cdi_sanitize_voie( $PointRetrait->nom ) . ' => ' .
							   $PointRetrait->adresse1 . ' ' .
							   $PointRetrait->adresse2 . ' ' .
							   $PointRetrait->codePostal . ' ' .
							   $PointRetrait->localite . ' => Distance: ' .
							   $PointRetrait->distanceEnMetre . 'm => Id: ' .
							$PointRetrait->identifiant
						);
						$pickupfulladdress = array();
						$pickupfulladdress['nom'] = cdi_c_Function::cdi_sanitize_voie( $PointRetrait->nom );
						$pickupfulladdress['adresse1'] = $PointRetrait->adresse1;
						$pickupfulladdress['adresse2'] = $PointRetrait->adresse2;
						$pickupfulladdress['adresse3'] = $PointRetrait->adresse3;
						$pickupfulladdress['codePostal'] = $PointRetrait->codePostal;
						$pickupfulladdress['localite'] = $PointRetrait->localite;
						$pickupfulladdress['codePays'] = $PointRetrait->codePays;
						$pickupfulladdress['libellePays'] = $PointRetrait->libellePays;
						WC()->session->set( 'cdi_pickupfulladdress', $pickupfulladdress );
						$pickupdetail .= 'Id: ' . $PointRetrait->identifiant . $eol;
						$pickupdetail .= 'Distance: ' . $PointRetrait->distanceEnMetre . 'm' . $eol;
						$pickupdetail .= $eol;
						$pickupdetail .= cdi_c_Function::cdi_sanitize_voie( $PointRetrait->nom ) . $eol;
						$pickupdetail .= $PointRetrait->adresse1 . $eol;
						if ( $PointRetrait->adresse2 ) {
							$pickupdetail .= $PointRetrait->adresse2 . $eol;
						}
						if ( $PointRetrait->adresse3 ) {
							$pickupdetail .= $PointRetrait->adresse3 . $eol;
						}
						$pickupdetail .= $PointRetrait->codePostal . ' ' . $PointRetrait->localite . $eol;
						$pickupdetail .= $PointRetrait->libellePays . $eol;

						if ( $PointRetrait->indiceDeLocalisation ) {
							$pickupdetail .= $eol . $PointRetrait->indiceDeLocalisation . $eol;
						}
						$pickupdetail .= $eol;

						if ( isset( $PointRetrait->StandardHoursOfOperation ) ) {
							$pickupdetail .= $PointRetrait->StandardHoursOfOperation . $eol;
						}
						if ( isset( $PointRetrait->horairesOuvertureLundi ) ) {
							$pickupdetail .= '    Lundi    ' . $PointRetrait->horairesOuvertureLundi . $eol;
						}
						if ( isset( $PointRetrait->horairesOuvertureMardi ) ) {
							$pickupdetail .= '    Mardi    ' . $PointRetrait->horairesOuvertureMardi . $eol;
						}
						if ( isset( $PointRetrait->horairesOuvertureMercredi ) ) {
							$pickupdetail .= '    Mercredi ' . $PointRetrait->horairesOuvertureMercredi . $eol;
						}
						if ( isset( $PointRetrait->horairesOuvertureJeudi ) ) {
							$pickupdetail .= '    Jeudi    ' . $PointRetrait->horairesOuvertureJeudi . $eol;
						}
						if ( isset( $PointRetrait->horairesOuvertureVendredi ) ) {
							$pickupdetail .= '    Vendredi ' . $PointRetrait->horairesOuvertureVendredi . $eol;
						}
						if ( isset( $PointRetrait->horairesOuvertureSamedi ) ) {
							$pickupdetail .= '    Samedi   ' . $PointRetrait->horairesOuvertureSamedi . $eol;
						}
						if ( isset( $PointRetrait->horairesOuvertureDimanche ) ) {
							$pickupdetail .= '    Dimanche ' . $PointRetrait->horairesOuvertureDimanche . $eol;
						}
						$pickupdetail .= $eol;

						$pickupdetail .= 'GPS: ' . $PointRetrait->coordGeolocalisationLatitude . ' ' . $PointRetrait->coordGeolocalisationLongitude . $eol;
						if ( isset( $PointRetrait->parking ) ) {
							$pickupdetail .= 'Parking: ' . $PointRetrait->parking . $eol;
						}
						if ( isset( $PointRetrait->accesPersonneMobiliteReduite ) ) {
							$pickupdetail .= 'Mobilité réduite: ' . $PointRetrait->accesPersonneMobiliteReduite . $eol;
						}
						if ( isset( $PointRetrait->langue ) ) {
							$pickupdetail .= 'Langue: ' . $PointRetrait->langue . $eol;
						}
						if ( isset( $PointRetrait->poidsMaxi ) ) {
							$pickupdetail .= 'Poids maxi: ' . $PointRetrait->poidsMaxi . $eol;
						}
						if ( isset( $PointRetrait->loanOfHandlingTool ) ) {
							$pickupdetail .= 'Equipements de manipulation: ' . $PointRetrait->loanOfHandlingTool . $eol;
						}
						if ( 'yes' == get_option( 'cdi_o_settings_selectclickonmap' ) ) {
							$pickupdetail = '';
						}
						$pickupdetail = apply_filters( 'cdi_filterhtml_retrait_displayselected', $pickupdetail, $PointRetrait );
						break;
					}
				}
			}
			// *********
			$resultmap = self::cdi_calculate_js_map();
			if ( ! $resultmap ) {
				$resultmap = array(
					'structure' => '',
					'script' => '',
				);
			}
			echo wp_json_encode( array( wp_kses_post( $pickupdetail ), wp_kses_post( $resultmap['structure'] ) . '<script>' . esc_js( $resultmap['script'] ) . '</script>' ) );
			wp_die();
		}
	}

	public static function cdi_check_exist_phonenumber($order) {
		// Check if phone must exist
		$chosen_shipping = WC()->session->get( 'cdi_refshippingmethod' );
		$arraychosen = explode( ':', $chosen_shipping ); // explode = method : instance : suffixe
		$arraychosenun = $arraychosen[0];
		$phonemandatory = get_option( 'cdi_o_settings_phonemandatory' );
		$arrayphonemandatory = explode( ',', $phonemandatory );
		$arrayphonemandatory = array_map( 'trim', $arrayphonemandatory );
		
		$cdi_selected_phone = $order->get_billing_phone() ; // First try billing phone as classic WC
		$cdi_selected_phone = cdi_c_Function::cdi_sanitize_phone( sanitize_text_field( $cdi_selected_phone ) );
		if (! $cdi_selected_phone) {
			$cdi_selected_phone = $order->get_shipping_phone() ; // Then try the new shipping phone of WC Bocks Checkout
			$cdi_selected_phone = cdi_c_Function::cdi_sanitize_phone( sanitize_text_field( $cdi_selected_phone ) );
		}

		foreach ( $arrayphonemandatory as $relation ) {
			if ( $relation && ( ( $relation == '*' ) or ( $relation == $arraychosen[0] ) or ( $relation == $arraychosenun ) ) ) {
				if ( !$cdi_selected_phone  or $cdi_selected_phone == '' ) {
					$msg = __( 'You must fill your billing phone number.' . $cdi_selected_phone, 'cdi' );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, $msg, 'msg' );
					throw new Exception( $msg );
				}
			}
		}
	}

	public static function cdi_check_pickup_isset() {
		// Check if pickup location is set
		global $woocommerce;
		$cdi_return_liste_points_livraison = WC()->session->get( 'cdi_return_liste_points_livraison' );
		if ( $cdi_return_liste_points_livraison ) {
			$cdipickuplocationid = WC()->session->get( 'cdi_pickuplocationid' );
			if ( ! $cdipickuplocationid ) {
				$msg = __( 'You must select a pickup location. Please try again.', 'cdi' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $msg, 'msg' );
				throw new Exception( $msg );
			} else {
				// check that pickup code product has not changed
				$listePointRetraitAcheminement = $cdi_return_liste_points_livraison->return->listePointRetraitAcheminement;
				$codeproductfound = WC()->session->get( 'cdi_forcedproductcode' );
				foreach ( $listePointRetraitAcheminement as $PointRetrait ) {
					if ( $cdipickuplocationid == $PointRetrait->identifiant && isset( $PointRetrait->typeDePoint ) ) {
						$codeproductfound = $PointRetrait->typeDePoint;
						break;
					}
				}
			}
			if ( empty( $codeproductfound ) ) { // error to catch
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $codeproductfound, 'tec' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $cdipickuplocationid, 'tec' );
				// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $listePointRetraitAcheminement, 'tec');
				$msg = __( 'Pickup location - Technical error on product code. Please try again.', 'cdi' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $msg, 'tec' );
				// throw new Exception( $msg );
			}
			WC()->session->set( 'cdi_forcedproductcode', $codeproductfound );
			// Check if WC bug : WC Address has been erased here in some cases
			if ( ! $woocommerce->customer->get_shipping_address() or ! $woocommerce->customer->get_shipping_city() or ! $woocommerce->customer->get_shipping_country() ) { // Adr inexistante in WC. Seems tobe a WC bug that may happens - Postcode may be not mandatory in some countries
				$msg = __( 'Pickup location - Technical error : WC adress not present. Please try again.', 'cdi' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $msg, 'tec' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $woocommerce->customer->get_shipping_address() . ' - ' . $woocommerce->customer->get_shipping_postcode() . ' - ' . $woocommerce->customer->get_shipping_city() . ' - ' . $woocommerce->customer->get_shipping_country(), 'tec' );
				throw new Exception( $msg );
			}
		}
	}

	public static function cdi_check_pickup_product_and_location() {
		// Check pickup product code but no pickup location
		$carrier = WC()->session->get( 'cdi_carrier' );
		$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_check_pickup_and_location';
		( $route )();
	}

	public static function cdi_check_pickup_method_and_nolocation() {
		// Check if pickup method but no no pickup location
		$chosen_shipping = WC()->session->get( 'cdi_refshippingmethod' );
		$cdipickuplocationid = WC()->session->get( 'cdi_pickuplocationid' );
		$testpickup = self::cdi_control_pickup_list( $chosen_shipping );
		if ( $testpickup['0'] == 1 and null == $cdipickuplocationid ) { // Technical error after this sequence : data missing,checkout,refresh,checkout
			$msg = __( 'Pickup location - Technical error on pickup method. Please try again.', 'cdi' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $msg . ' ' . $chosen_shipping, 'tec' );
			throw new Exception( $msg );
		}
	}

	public static function cdi_woocommerce_checkout_posted_data( $data ) {
		// Action when checkout button is pressed
		global $woocommerce;
		// set again shipping and products as seen by WC before the order is created
		self::cdi_get_shipping_and_product();
		$chosen_shipping = WC()->session->get( 'cdi_refshippingmethod' );
		$chosen_products = WC()->session->get( 'cdi_chosen_products' );
		$shipping_method_name = WC()->session->get( 'cdi_shipping_method_name' );

		$cdipickuplocationid = WC()->session->get( 'cdi_pickuplocationid' );
		$cdipickuplocationlabel = WC()->session->get( 'cdi_pickuplocationlabel' );
		$codeproductfound = WC()->session->get( 'cdi_forcedproductcode' );

		$cdicarrier = WC()->session->get( 'cdi_carrier' );
		$cdipickupfulladdress = WC()->session->get( 'cdi_pickupfulladdress' );

		$cdi_return_ws_liste_points_livraison = WC()->session->get( 'cdi_return_ws_liste_points_livraison' );

		// Check if pickup location is set
		self::cdi_check_pickup_isset();
		$codeproductfound = WC()->session->get( 'cdi_forcedproductcode' );

		$debug = '*** At ckeckout, data passed to WC before order : ' . 'Product: ' . $codeproductfound . ' Location: ' . $cdipickuplocationid . ' Label: ' . $cdipickuplocationlabel . ' Method: ' . $chosen_shipping . ' ***';
		cdi_c_Function::cdi_debug( __LINE__, __FILE__, $debug, 'msg' );

		// Check if pickup product code but no pickup location
		self::cdi_check_pickup_product_and_location();

		// Check if pickup method but no no pickup location
		self::cdi_check_pickup_method_and_nolocation();

		// Now pass cdi datas with WC custom fields
		$data['cdi_refshippingmethod'] = $chosen_shipping;
		$data['cdi_chosen_products'] = $chosen_products;
		$data['cdi_shipping_method_name'] = $shipping_method_name;
		$data['cdi_pickuplocationid'] = $cdipickuplocationid;
		$data['cdi_pickuplocationlabel'] = $cdipickuplocationlabel;
		$data['cdi_forcedproductcode'] = $codeproductfound;
		$data['cdi_carrier'] = $cdicarrier;
		$data['cdi_pickupfulladdress'] = $cdipickupfulladdress;
		return $data;
	}

	public static function cdi_woocommerce_checkout_update_order_meta( $order_id, $data ) {
		global $woocommerce;
		// Here the order exist. So we can store data in meta
		$order = wc_get_order( $order_id );

		// Mandatory phone number check here
		self::cdi_check_exist_phonenumber($order) ;

		$order->update_meta_data( '_cdi_meta_productCode', $data['cdi_forcedproductcode'] );
		$order->update_meta_data( '_cdi_meta_pickupLocationId', $data['cdi_pickuplocationid'] );
		$order->update_meta_data( '_cdi_meta_lastpickupLocationId', $data['cdi_pickuplocationid'] ); // To avoid overhead in order list when changed
		$order->update_meta_data( '_cdi_meta_pickupLocationlabel', $data['cdi_pickuplocationlabel'] );
		$order->update_meta_data( '_cdi_refshippingmethod', $data['cdi_refshippingmethod'] );
		$order->update_meta_data( '_cdi_chosen_products', $data['cdi_chosen_products'] );
		$order->update_meta_data( '_cdi_meta_shippingmethod_name', $data['cdi_shipping_method_name'] );
		$order->update_meta_data( '_cdi_meta_carrier', $data['cdi_carrier'] );
		$order->update_meta_data( '_cdi_meta_pickupfulladdress', $data['cdi_pickupfulladdress'] );
		$order->save();
		$debug = '*** At ckeckout, data passed to WC after order : ' . 'Product: ' . $data['cdi_forcedproductcode'] . ' Location: ' . $data['cdi_pickuplocationid'] . ' Label: ' . $data['cdi_pickuplocationlabel'] . ' Method: ' . $data['cdi_refshippingmethod'] . ' Carrier: ' . $data['cdi_carrier'] . ' ***';
		cdi_c_Function::cdi_debug( __LINE__, __FILE__, $debug, 'msg' );
	}

	public static function cdi_wp_footer() {
		if ( cdi_c_Function::cdi_is_checkout() ) { // No useful to do this if not the checkout page
			?>
	  <!-- CDI version : <?php echo esc_attr( get_option( 'cdi_o_version' ) ); ?> --><?php
		}
	}

	public static function cdi_ex_filterbool_tobeornottobe_shipping_rate( $eligible, $rateid ) {
		// Must we show pickup shipping tariff ?
		$new_eligible = $eligible;
		if ( $new_eligible === true and get_option( 'cdi_o_settings_pickupoffline' ) !== 'no' ) {
			$array_return = self::cdi_control_pickup_list( $rateid );
			if ( $array_return[0] == '1' ) {
				if ( self::cdi_test_carrier() === false ) { // Test if Carrier is in line
					return false;
				}
			}
		}
		return $new_eligible;
	}
}


?>
