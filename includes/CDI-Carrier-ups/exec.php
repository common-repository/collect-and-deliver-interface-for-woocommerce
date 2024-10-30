<?php
/*
 * Plugin Name: CDI - Collect and Deliver Interface
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/****************************************************************************************/
/* Carrier UPS                                                                          */
/****************************************************************************************/

class cdi_c_Carrier_ups {
	public static function init() {
		include_once dirname( __FILE__ ) . '/Ups-Affranchissement.php';
		cdi_c_Ups_Affranchissement::init();
		include_once dirname( __FILE__ ) . '/Ups-Retourcolis.php';
		cdi_c_Ups_Retourcolis::init();
		include_once dirname( __FILE__ ) . '/Ups-Get-Oauthcode.php';		
		cdi_c_Ups_Get_Oauthcode::init();		
	}

	/****************************************************************************************/
	/* Carrier References Livraisons                                                        */
	/****************************************************************************************/

	public static function cdi_carrier_update_settings() {
		$forcesettings = get_option( 'cdi_o_settings_ups_defautsettings' );
		if ( $forcesettings == 'yes' ) {
			$arrdefaut_ups_pickups = array( 'cdi_shipping_ups_pick1', 'cdi_shipping_ups_pick2' );
			$pickuplist = str_replace( ' ', '', get_option( 'cdi_o_settings_pickupmethodnames' ) );
			$arraypickuplist = explode( ',', $pickuplist );
			$arraypickuplist = array_map( 'trim', $arraypickuplist );
			foreach ( $arrdefaut_ups_pickups as $defaut_ups_pickup ) {
				if ( ! in_array( $defaut_ups_pickup, $arraypickuplist ) ) {
					$pickuplist = trim( $pickuplist, ' ,' );
					$pickuplist = $pickuplist . ',' . $defaut_ups_pickup;
					$pickuplist = str_replace( ',,', ',', $pickuplist );
					$pickuplist = trim( $pickuplist, ' ,' );
					update_option( 'cdi_o_settings_pickupmethodnames', $pickuplist );
				}
			}
			$arrdefaut_ups_products = array( 'cdi_shipping_ups_home1=11', 'cdi_shipping_ups_home2=07', 'cdi_shipping_ups_pick1=AP' );
			$productlist = str_replace( ' ', '', get_option( 'cdi_o_settings_forcedproductcodes' ) );
			$arrayproductlist = explode( ',', $productlist );
			$arrayproductlist = array_map( 'trim', $arrayproductlist );
			foreach ( $arrdefaut_ups_products as $defaut_ups_product ) {
				if ( ! in_array( $defaut_ups_product, $arrayproductlist ) ) {
					$productlist = trim( $productlist, ' ,' );
					$productlist = $productlist . ',' . $defaut_ups_product;
					$productlist = str_replace( ',,', ',', $productlist );
					$productlist = trim( $productlist, ' ,' );
					update_option( 'cdi_o_settings_forcedproductcodes', $productlist );
				}
			}

			$arrdefaut_ups_mandatoryphones = array( 'cdi_shipping_ups_pick1', 'cdi_shipping_ups_pick2' );
			$mandatoryphonelist = str_replace( ' ', '', get_option( 'cdi_o_settings_phonemandatory' ) );
			$arraymandatoryphonelist = explode( ',', $mandatoryphonelist );
			$arraymandatoryphonelist = array_map( 'trim', $arraymandatoryphonelist );
			foreach ( $arrdefaut_ups_mandatoryphones as $defaut_ups_mandatoryphone ) {
				if ( ! in_array( $defaut_ups_mandatoryphone, $arraymandatoryphonelist ) ) {
					$mandatoryphonelist = trim( $mandatoryphonelist, ' ,' );
					$mandatoryphonelist = $mandatoryphonelist . ',' . $defaut_ups_mandatoryphone;
					$mandatoryphonelist = str_replace( ',,', ',', $mandatoryphonelist );
					$mandatoryphonelist = trim( $mandatoryphonelist, ' ,' );
					update_option( 'cdi_o_settings_phonemandatory', $mandatoryphonelist );
				}
			}
		}
	}

	public static function cdi_isit_pickup_authorized() {
		return true;
	}

	public static function cdi_test_carrier() {
		return true;
	}

	public static function cdi_get_points_livraison( $relaytype ) {
		global $woocommerce;
		global $msgtofrontend;
		if ( self::cdi_test_carrier() === false ) {
			return false;
		}
		// Shipping address GPS
		$arraygps = cdi_c_Function::cdi_geolocate_customer();
		if ( ! $arraygps ) {
			return false;
		}
		$lat = $arraygps['lat'];
		$lon = $arraygps['lon'];
		$addresscustomer = $arraygps['addresscustomer'];

		$Pays = $woocommerce->customer->get_shipping_country();
		$NumPointRelais = '';
		$Ville = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_string( cdi_c_Function::cdi_sanitize_accents( $woocommerce->customer->get_shipping_city() ) ) );
		$CP = $woocommerce->customer->get_shipping_postcode();
		$Latitude = $lat;
		$Longitude = $lon;

		$Poids = (float) $woocommerce->cart->cart_contents_weight;
		if ( get_option( 'woocommerce_weight_unit' ) == 'kg' ) { // Convert kg to g
			$Poids = $Poids * 1000;
		}
		$Poids = round( $Poids + get_option( 'cdi_o_settings_parceltareweight' ) );
		if ( ! $Poids or $Poids == 0 ) {
			$Poids = 100;
		}

		$errorws = null;
		include_once( 'ups-access-context.php' );
		$NombreResultats = 20;

		// ****** Locator Request
		$headers = null ;								
		$token = cdi_c_Ups_Get_Oauthcode::cdi_Ups_OAuth_get_token() ;						
		if ($token) {
			//set headers with token
			$headers = [
				'Authorization: Bearer ' . $token,				    	
				'Content-Type: application/json',									
 				'transId: CDI-' . 'Locator -' . time(),
 				'transactionSrc: CDI-Wordpress-WooCommerce',  
				];	
		}
		
		// Create Locator Request XMl
		$locatorRequestXML = new SimpleXMLElement( '<LocatorRequest ></LocatorRequest >' );
		$request = $locatorRequestXML->addChild( 'Request' );
		$request->addChild( 'RequestAction', 'Locator' );
		$request->addChild( 'RequestOption', '56' );
		$originAddress = $locatorRequestXML->addChild( 'OriginAddress' );
		$originAddress->addChild( 'MaximumListSize', $NombreResultats );		
		if ($Latitude and $Longitude) {
			$Geocode = $originAddress->addChild( 'Geocode' );	
			$Geocode->addChild( 'Latitude', $Latitude );		
			$Geocode->addChild( 'Longitude', $Longitude );
		}		
		$addressKeyFormat = $originAddress->addChild( 'AddressKeyFormat' );
		$addressKeyFormat->addChild( 'AddressLine', $addresscustomer );
		$addressKeyFormat->addChild( 'AddressLine2', '' );
		$addressKeyFormat->addChild( 'AddressLine3', '' );
		$addressKeyFormat->addChild( 'PoliticalDivision2', $Ville );
		$addressKeyFormat->addChild( 'PoliticalDivision1', '' ); // STATE if EXISTE
		$addressKeyFormat->addChild( 'PostcodePrimaryLow', $CP );
		$addressKeyFormat->addChild( 'CountryCode', $Pays );		
		$translate = $locatorRequestXML->addChild( 'Translate' );
		$translate->addChild( 'Locale', 'fr_FR' );
		$unitOfMeasurement = $locatorRequestXML->addChild( 'UnitOfMeasurement' );
		$unitOfMeasurement->addChild( 'Code', 'KM' );		
		$locationSearchCriteria = $locatorRequestXML->addChild( 'LocationSearchCriteria' );
		$locationSearchCriteria->addChild( 'MaximumListSize', $NombreResultats );		
		
		// Convert XML to RESTful (array)				
		$requestXML = $locatorRequestXML->asXML();									
		$payload = array( "LocatorRequest" => json_decode( json_encode( (array) simplexml_load_string( $requestXML ) ), true ) );
/* Compressed log for CDI debug */ cdi_c_Function::cdi_debug( __LINE__, __FILE__, json_encode($payload), 'tec' );	
		$response = cdi_c_Function::cdi_url_post_remote( $urllocator, $payload, $headers );
/* Compressed log for CDI debug */ cdi_c_Function::cdi_debug( __LINE__, __FILE__, $response, 'tec' );

		// Response processing
		$arrayresponse = json_decode( $response, true );
		if (isset( $arrayresponse['response']['errors'] )) {
			// Technical or Authentification error							
			$returnerrcode = $arrayresponse['response']['errors']['0']['code'];
			$returnerrlibelle = $arrayresponse['response']['errors']['0']['message'];
		}else{
			// API error									
			$returnerrcode = $arrayresponse['LocatorResponse']['Response']['ResponseStatusCode'];
			$returnerrlibelle = $arrayresponse['LocatorResponse']['Response']['ResponseStatusDescription'];
		}								
		if ( $returnerrcode != 1 ) {
			$errorws = __( ' ===> Search Access Points error - ', 'cdi' ) . 'UPS : ' . $returnerrcode . ' : ' . $returnerrlibelle;
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $errorws, 'tec' );			
		} else {
			// Parse each Point
			$dropLocations = $arrayresponse['LocatorResponse']['SearchResults']['DropLocation'];
			$returnrelays = array();
			foreach ( $dropLocations as $point ) {
				$arr = array(
					'identifiant' => ltrim( $point['LocationID'] ),
					'codePays' => $point['AddressKeyFormat']['CountryCode'],
					'langue' => 'FR',
					'libellePays' => WC()->countries->countries[ $point['AddressKeyFormat']['CountryCode'] ],
					'loanOfHandlingTool' => null,
					'parking' => null,
					'reseau' => '',      // Mettre dynamiquement code product
					'accesPersonneMobiliteReduite' => null,

					'nom' => substr( rtrim( cdi_c_Function::cdi_sanitize_voie( $point['AddressKeyFormat']['ConsigneeName'] ) ), 0, 35 ),
					'adresse1' => substr( rtrim( cdi_c_Function::cdi_sanitize_voie( $point['AddressKeyFormat']['AddressLine'] ) ), 0, 35 ),
					'adresse2' => '',
					'adresse3' => '',
					'localite' => substr( rtrim( cdi_c_Function::cdi_sanitize_voie( $point['AddressKeyFormat']['PoliticalDivision2'] ) ), 0, 30 ),
					'codePostal' => preg_replace( '/[^a-zA-Z0-9\s]/', '', $point['AddressKeyFormat']['PostcodePrimaryLow'] ),
					'indiceDeLocalisation' => rtrim( cdi_c_Function::cdi_sanitize_voie( '' ) ),                             // 'phone' => $point['PhoneNumber'] ,

					'coordGeolocalisationLatitude' => (float) str_replace( ',', '.', $point['Geocode']['Latitude'] ),
					'coordGeolocalisationLongitude' => (float) str_replace( ',', '.', $point['Geocode']['Longitude'] ),
					'distanceEnMetre' => $point['Distance']['Value'] * 1000,
					'StandardHoursOfOperation' => $point['StandardHoursOfOperation'],
				);
				$obj = (object) $arr;
				$returnrelays[] = $obj;
			}
			$listrelays = (object) array(
				'errorCode' => 0,
				'errorMessage' => 'Code retour OK',
				'listePointRetraitAcheminement' => $returnrelays,
			);
			$return = (object) array( 'return' => $listrelays );
		}
		// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $PointsRelais, 'tec');
		if ( $errorws ) {
			return false;
		} else {
			return $return;
		}
	}

	public static function cdi_check_pickup_and_location() {
		$codeproductfound = WC()->session->get( 'cdi_forcedproductcode' );
		$cdipickuplocationid = WC()->session->get( 'cdi_pickuplocationid' );
		if ( in_array( $codeproductfound, array( '70' ) ) and empty( $cdipickuplocationid ) ) { // error to catch
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $codeproductfound, 'tec' );
			throw new Exception( __( 'Pickup location - Technical error on pickup product code vs location id. Please try again.', 'cdi' ) );
		}
	}

	/****************************************************************************************/
	/* Carrier Front-end tracking                                                           */
	/****************************************************************************************/

	public static function cdi_text_preceding_trackingcode() {
		$return = __( get_option( 'cdi_o_settings_ups_text_preceding_trackingcode' ), 'cdi' );
		return $return;
	}

	public static function cdi_url_trackingcode() {
		$return = get_option( 'cdi_o_settings_ups_url_trackingcode' );
		return $return;
	}

	/****************************************************************************************/
	/* CDI Metabox in order panel                                                           */
	/****************************************************************************************/

	public static function cdi_metabox_initforcarrier( $order_id, $order ) {
		$max = $order->get_meta( '_cdi_meta_limitquote' );
		if ( ! $max ) {
			$max = floor( get_option( 'cdi_o_settings_ups_rateshippingcost' ) * $order->get_total() ) / 100;
			$abs = get_option( 'cdi_o_settings_ups_absmaxshippingcost' );
			if ( $abs > $max ) {
				$max = $abs;
			}
			$order->update_meta_data( '_cdi_meta_limitquote', $max );
			$order->save();
		}
		return true;
	}

	public static function cdi_metabox_tracking_zone( $order_id, $order ) {
		?>
		<div style='background-color:#eeeeee; color:#000000; width:100%;'>Tracking zone</div><p style="clear:both"></p>

		<p style='width:35%; float:left;  margin-top:5px;'><a><?php _e( 'Tracking code : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_text_input(
																			array(
																				'name' => '_cdi_meta_tracking',
																				'type' => 'text',
																				'style' => 'width:60%; float:left;',
																				'id'   => '_cdi_meta_tracking',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>

		  <?php $cdi_parcelNumberPartner = $order->get_meta( '_cdi_meta_parcelNumberPartner' ); ?>  
		  <?php if ( $cdi_parcelNumberPartner ) { ?>  
				<p><a><?php _e( 'Partner number : ', 'cdi' ); ?></a><a style='color:black'><?php echo esc_attr( $cdi_parcelNumberPartner ); ?> </a></p>
		<?php } ?>

		  <?php $cdi_urllabel = $order->get_meta( '_cdi_meta_urllabel' ); ?>
		  <?php if ( $cdi_urllabel ) { ?> 
				<p><a style="display:inline-block;"><?php _e( 'To Labels :  ', 'cdi' ); ?></a><a style="vertical-align: middle; display:inline-block; color:black; width:12em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" href="<?php echo esc_url( $cdi_urllabel ); ?>" onclick="window.open(this.href); return false;" > <?php echo esc_url( $cdi_urllabel ); ?> </a></p>
		<?php } ?>

		  <?php if ( $order->get_meta( '_cdi_meta_exist_uploads_label' ) == true or $order->get_meta( '_cdi_meta_exist_uploads_cn23' ) == true ) { ?>
		  <p style="display:inline-block; margin:0px;">
		<?php } ?>

			 <?php if ( $order->get_meta( '_cdi_meta_exist_uploads_label' ) == true ) { ?>
					 <form method="post" id="cdi_local_label_pdf" action="" style="display:inline-block;">
					  <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
					  <input type="submit" name="cdi_local_label_pdf" value="Print label"  title="Print label" /> 
					  <?php wp_nonce_field( 'cdi_local_label_pdf', 'cdi_local_label_pdf_nonce' ); ?> 
					</form>
		   <?php } ?>

			 <?php if ( $order->get_meta( '_cdi_meta_exist_uploads_cn23' ) == true ) { ?>
					 <form method="post" id="cdi_local_cn23_pdf" action="" style="display:inline-block;">
					  <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
					  <input type="submit" name="cdi_local_cn23_pdf" value="Print Cn23"  title="Print cn23" /> 
					  <?php wp_nonce_field( 'cdi_local_cn23_pdf', 'cdi_local_cn23_pdf_nonce' ); ?> 
					</form>
		   <?php } ?>
		</p>
		<?php

	}

	public static function cdi_metabox_parcel_settings( $order_id, $order ) {
		$max = $order->get_meta( '_cdi_meta_limitquote' );
		if ( ! $max ) {
			$max = floor( get_option( 'cdi_o_settings_ups_rateshippingcost' ) * $order->get_total() ) / 100;
			$order->update_meta_data( '_cdi_meta_limitquote', $max );
			$order->save();
		}
		?>
		<div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Parcel parameters', 'cdi' ); ?></div><p style="clear:both"></p>
		<p style='width:25%; float:left;  margin-top:5px;'><a><?php _e( 'Parcel : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_typeparcel',
																				'type' => 'text',
																				'options' => array(
																					'colis-standard'   => __( 'Standard', 'cdi' ),
																					'colis-volumineux' => __( 'Cumbersome', 'cdi' ),
																					'colis-rouleau   ' => __( 'Tube', 'cdi' ),
																				),
																				'style' => 'width:70%; float:left;',
																				'id'   => '_cdi_meta_typeparcel',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>

		<p style='width:25%; float:left;  margin-top:5px;'><a><?php _e( 'Weight : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_text_input(
																			array(
																				'name' => '_cdi_meta_parcelweight',
																				'type' => 'text',
																				'data_type' => 'decimal',
																				'style' => 'width:70%; float:left;',
																				'id'   => '_cdi_meta_parcelweight',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>
		<p style='width:35%; float:left;  margin-top:5px;'><a><?php _e( 'Cost max/real ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_text_input(
																			array(
																				'name' => '_cdi_meta_limitquote',
																				'type' => 'text',
																				'data_type' => 'decimal',
																				'style' => 'width:30%; float:left;',
																				'id'   => '_cdi_meta_limitquote',
																				'label' => '',
																			), $order,
																		);
																		?>
			 / <?php echo esc_attr( $order->get_meta( '_cdi_meta_realquote' ) ); ?></a></p><p style="clear:both"></p>
		<?php
	}

	public static function cdi_metabox_optional_choices( $order_id, $order ) {
	}

	public static function cdi_metabox_shipping_customer_choices( $order_id, $order ) {
		?>
		<!--  Pickup location web services - can be filled by meta box or retraitpoint web services --> 
		<div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Customer shipping settings :', 'cdi' ); ?></div><p style="clear:both"></p>
		<p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'Forced product code : ', 'cdi' ); ?>
																	   <?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_productCode',
																				'type' => 'text',
																				'options' => array(
																					'' => '',
																					'11' => __( '11 - UPS Standard', 'cdi' ),
																					'07' => __( '07 - UPS Express', 'cdi' ),
																					'AP' => __( 'AP - UPS Access Point', 'cdi' ),
																				),
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_productCode',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>
		<p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'Pickup location id : ', 'cdi' ); ?>
																	   <?php
																		woocommerce_wp_text_input(
																			array(
																				'name' => '_cdi_meta_pickupLocationId',
																				'type' => 'text',
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_pickupLocationId',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>

		  <?php $pickupLocationlabel = $order->get_meta( '_cdi_meta_pickupLocationlabel' ); ?>
		  <?php if ( $pickupLocationlabel ) { ?>
				<?php $pickupLocationlabel = stristr( $pickupLocationlabel, '=> Distance: ', true ); ?>
				<p><a><?php _e( 'Location : ', 'cdi' ); ?></a><a style='color:black'><?php echo wp_kses_post( $pickupLocationlabel ); ?> </a></p>
		<?php } ?>
		<!--  End Pickup location web services --> 
		<?php
	}

	public static function cdi_metabox_shipping_cn23( $order_id, $order ) {
		?>
		<?php $shipping_country = $order->get_shipping_country(); ?>
		<?php $shipping_postcode = $order->get_shipping_postcode(); ?>
		<?php
		if ( cdi_c_Function::cdi_cn23_country( $shipping_country, $shipping_postcode ) ) {
			?>
			 <!--  CN23 display --> 
		<div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'CN23 parameters', 'cdi' ); ?></div><p style="clear:both"></p>

		<p style='width:50%; float:left;  margin-top:5px;'><a><?php _e( 'CN23 transport : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_text_input(
																			array(
																				'name' => '_cdi_meta_cn23_shipping',
																				'type' => 'text',
																				'data_type' => 'decimal',
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_cn23_shipping',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>

		<p style='width:50%; float:left;  margin-top:5px;'><a><?php _e( 'CN23 category : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_cn23_category',
																				'type' => 'text',
																				'options' => array(
																					'1' => __( 'Gift', 'cdi' ),
																					'2' => __( 'Sample', 'cdi' ),
																					'3' => __( 'Commercial', 'cdi' ),
																					'4' => __( 'Documents', 'cdi' ),
																					'5' => __( 'Other', 'cdi' ),
																					'6' => __( 'Returned goods', 'cdi' ),
																				),
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_cn23_category',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>

			<?php $items = cdi_c_Function::cdi_get_items_chosen( $order ); ?>
			<?php $nbart = 0; ?>
			<?php foreach ( $items as $item ) { ?>
		  <div style='background-color:#eeeeee; color:#000000; width:100%; height:8px; font-size:smaller; line-height:8px;'><?php _e( 'Article : ', 'cdi' ); ?><?php echo esc_attr( $nbart ); ?></div><p style="clear:both"></p>
				  <p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'CN23 Art descript. : ', 'cdi' ); ?>
																				 <?php
																					woocommerce_wp_text_input(
																						array(
																							'name' => '_cdi_meta_cn23_article_description_' . $nbart,
																							'type' => 'text',
																							'style' => 'width:45%; float:left;',
																							'id'   => '_cdi_meta_cn23_article_description_' . $nbart,
																							'label' => '',
																						), $order,
																					);
																					?>
			   	</a></p><p style="clear:both"></p>
								<p style='width:50%; float:left;  margin-top:5px;'><a><?php _e( 'CN23 Art weight : ', 'cdi' ); ?>
				<?php
				woocommerce_wp_text_input(
					array(
						'name' => '_cdi_meta_cn23_article_weight_' . $nbart,
						'type' => 'text',
						'data_type' => 'decimal',
						'style' => 'width:45%; float:left;',
						'id'   => '_cdi_meta_cn23_article_weight_' . $nbart,
						'label' => '',
					), $order,
				);
				?>
			   </a></p><p style="clear:both"></p>
				  <p style='width:50%; float:left;  margin-top:5px;'><a><?php _e( 'CN23 Art quantity : ', 'cdi' ); ?>
																				  <?php
																					woocommerce_wp_text_input(
																						array(
																							'name' => '_cdi_meta_cn23_article_quantity_' . $nbart,
																							'type' => 'text',
																							'data_type' => 'decimal',
																							'style' => 'width:45%; float:left;',
																							'id'   => '_cdi_meta_cn23_article_quantity_' . $nbart,
																							'label' => '',
																						), $order,
																					);
																					?>
			   </a></p><p style="clear:both"></p>
				  <p style='width:50%; float:left;  margin-top:5px;'><a><?php _e( 'CN23 Art value : ', 'cdi' ); ?>
																				  <?php
																					woocommerce_wp_text_input(
																						array(
																							'name' => '_cdi_meta_cn23_article_value_' . $nbart,
																							'type' => 'text',
																							'data_type' => 'decimal',
																							'style' => 'width:45%; float:left;',
																							'id'   => '_cdi_meta_cn23_article_value_' . $nbart,
																							'label' => '',
																						), $order,
																					);
																					?>
			   </a></p><p style="clear:both"></p>

				<?php
				if ( $order->get_meta( '_cdi_meta_cn23_category' ) == '3' ) {
					?>
					 <!--  CN23 HS code display --> 

			<p style='width:50%; float:left; margin-top:5px;'><a> <a href="https://www.douane.gouv.fr/service-en-ligne" target="_blank">HS code</a> : 
					<?php
					woocommerce_wp_text_input(
						array(
							'name' => '_cdi_meta_cn23_article_hstariffnumber_' . $nbart,
							'type' => 'text',
							'custom_attributes' => array(
								'pattern' => '[0-9]{4,10}',
							),
							'style' => 'width:45%; float:left;',
							'id'   => '_cdi_meta_cn23_article_hstariffnumber_' . $nbart,
							'label' => '',
						), $order,
					);
					?>
				 </a></p><p style="clear:both"></p>

		  <?php } ?> <!--  End CN23 HS code display --> 
				  <p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'CN23 Art origine : ', 'cdi' ); ?>
																				 <?php
																					woocommerce_wp_text_input(
																						array(
																							'name' => '_cdi_meta_cn23_article_origincountry_' . $nbart,
																							'type' => 'text',
																							'style' => 'width:45%; float:left;',
																							'id'   => '_cdi_meta_cn23_article_origincountry_' . $nbart,
																							'label' => '',
																						), $order,
																					);
																					?>
			   </a></p><p style="clear:both"></p>
				<?php $nbart = $nbart + 1; ?>
		<?php } ?>
		<?php } ?> <!--  End CN23 display --> 
		<?php
	}

	public static function cdi_metabox_parcel_return( $order_id, $order ) {
		?>
		<?php
		if ( get_option( 'cdi_o_settings_ups_parcelreturn' ) == 'yes' ) {
			?>
			 <!--  Parcel return display --> 
		  <div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Parcel return', 'cdi' ); ?></div>        		  
		  	<p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'Return days : ', 'cdi' ); ?>
																					  <?php
																						woocommerce_wp_text_input(
																							array(
																								'name' => '_cdi_meta_nbdayparcelreturn',
																								'type' => 'text',
																								'data_type' => 'decimal',
																								'style' => 'width:45%; float:left;',
																								'id'   => '_cdi_meta_nbdayparcelreturn',
																								'label' => '',
																							), $order,
																						);
																						?>
			   </a></p><p style="clear:both"></p>

			<?php if ( $order->get_meta( '_cdi_meta_base64_return' ) ) { ?>
			   <p style="display:inline-block; margin:0px;">
			   <form method="post" id="cdi_admin_return_pdf" action="" style="display:inline-block;">
				 <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
				 <input type="submit" name="cdi_admin_return_pdf" value="Print label"  title="Print return label" /> 
				 <?php wp_nonce_field( 'cdi_admin_return_pdf', 'cdi_admin_return_pdf_nonce' ); ?> 
			   </form>
  
				<?php if ( $order->get_meta( '_cdi_meta_base64_returncn23' ) ) { ?>
				 <p style="display:inline-block; margin:0px;">    
				 <form method="post" id="cdi_admin_returncn23_pdf" action="" style="display:inline-block;">
				   <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
				   <input type="submit" name="cdi_admin_returncn23_pdf" value="Print Cn23"  title="Print return Cn23" /> 
					<?php wp_nonce_field( 'cdi_admin_returncn23_pdf', 'cdi_admin_returncn23_pdf_nonce' ); ?> 
				 </form>          
			<?php } else { ?>           
				 <p style="display:inline-block; margin:0px;">    
				 <form method="post" id="cdi_admin_createreturncn23_pdf" action="" style="display:inline-block; padding-left:30px;">
				   <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
				   <input type="submit" name="cdi_admin_createreturncn23_pdf" value="Cn23"  title="<?php _e( 'Force the creation of a Cn23 return form for this order. Only the administrator can generate it and print it to send it by email to his client. The Internet customer will not see it in its order view.', 'cdi' ); ?>" style='width: 60px; height: 30px; border: solid 1px #000; border-radius: 50%;'  /> 
					<?php wp_nonce_field( 'cdi_admin_createreturncn23_pdf', 'cdi_admin_createreturncn23_pdf_nonce' ); ?> 
				 </form> 
			<?php } ?> 

				<?php $cdi_tracking_return = $order->get_meta( '_cdi_meta_parcelnumber_return' ); ?>  
			  <p><a><?php _e( 'Return tracking code : ', 'cdi' ); ?></a><a style='color:black'><?php echo esc_attr( $cdi_tracking_return ); ?> </a></p>

		   </p>
		   <?php } else { ?>         
				<?php if ( cdi_c_Retour_Colis::cdi_check_returnlabel_eligible( $order_id ) == true ) { ?>   
			   <p style="display:inline-block; margin:0px;">    
			   <form method="post" id="cdi_admin_createreturnlabel_pdf" action="" style="display:inline-block; padding-left:30px;">
				 <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
				 <input type="submit" name="cdi_admin_createreturnlabel_pdf" value="Label"  title="<?php _e( 'Force the creation of a Label return form for this order. It will be seen by the admin, and the Internet customer in his order view. ', 'cdi' ); ?>" style='width: 60px; height: 30px; border: solid 1px #000; border-radius: 50%;'  /> 
					<?php wp_nonce_field( 'cdi_admin_createreturnlabel_pdf', 'cdi_admin_createreturnlabel_pdf_nonce' ); ?> 
			   </form> 
			<?php } ?>
		  <?php } ?>

		<?php } ?> <!--  End Parcel return display --> 
		<?php
	}

	public static function cdi_metabox_shipping_updatepickupaddress( $order_id, $order ) {
		global $woocommerce;
		$pickupLocationId = $order->get_meta( '_cdi_meta_pickupLocationId' );
		$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $order_id );
		$errorws = null;

		include_once( 'ups-access-context.php' );
		$NombreResultats = 20;

		// ****** Locator Request
		$headers = null ;								
		$token = cdi_c_Ups_Get_Oauthcode::cdi_Ups_OAuth_get_token() ;						
		if ($token) {
			//set headers with token
			$headers = [
				'Authorization: Bearer ' . $token,				    	
				'Content-Type: application/json',									
 				'transId: CDI-' . 'Locator -' . time(),
 				'transactionSrc: CDI-Wordpress-WooCommerce',  
				];	
		}
		
		// Create Locator Request XMl
		$locatorRequestXML = new SimpleXMLElement( '<LocatorRequest ></LocatorRequest >' );
		$request = $locatorRequestXML->addChild( 'Request' );
		$request->addChild( 'RequestAction', 'Locator' );
		$request->addChild( 'RequestOption', '56' );
		$originAddress = $locatorRequestXML->addChild( 'OriginAddress' );
		$originAddress->addChild( 'MaximumListSize', $NombreResultats );
		
		$addressKeyFormat = $originAddress->addChild( 'AddressKeyFormat' );
		$addressKeyFormat->addChild( 'AddressLine', $array_for_carrier['shipping_address_1'] );
		$addressKeyFormat->addChild( 'AddressLine2', $array_for_carrier['shipping_address_2'] );
		$addressKeyFormat->addChild( 'AddressLine3', $array_for_carrier['shipping_address_3'] );
		$addressKeyFormat->addChild( 'PoliticalDivision2', $array_for_carrier['shipping_city'] );
		$addressKeyFormat->addChild( 'PoliticalDivision1', $array_for_carrier['shipping_state'] );
		$addressKeyFormat->addChild( 'PostcodePrimaryLow', $array_for_carrier['shipping_postcode'] );
		$addressKeyFormat->addChild( 'CountryCode', $array_for_carrier['shipping_country'] );
		
		$translate = $locatorRequestXML->addChild( 'Translate' );
		$translate->addChild( 'Locale', 'fr_FR' );
		$unitOfMeasurement = $locatorRequestXML->addChild( 'UnitOfMeasurement' );
		$unitOfMeasurement->addChild( 'Code', 'KM' );		
		$locationSearchCriteria = $locatorRequestXML->addChild( 'LocationSearchCriteria' );
		$locationSearchCriteria->addChild( 'MaximumListSize', $NombreResultats );		
		
		// Convert XML to RESTful (array)				
		$requestXML = $locatorRequestXML->asXML();									
		$payload = array( "LocatorRequest" => json_decode( json_encode( (array) simplexml_load_string( $requestXML ) ), true ) );
/* Compressed log for CDI debug */ cdi_c_Function::cdi_debug( __LINE__, __FILE__, json_encode($payload), 'tec' );	
		$response = cdi_c_Function::cdi_url_post_remote( $urllocator, $payload, $headers );
/* Compressed log for CDI debug */ cdi_c_Function::cdi_debug( __LINE__, __FILE__, $response, 'tec' );

		// Response processing
		$arrayresponse = json_decode( $response, true );
		if (isset( $arrayresponse['response']['errors'] )) {
			// Technical or Authentification error							
			$returnerrcode = $arrayresponse['response']['errors']['0']['code'];
			$returnerrlibelle = $arrayresponse['response']['errors']['0']['message'];
		}else{
			// API error									
			$returnerrcode = $arrayresponse['LocatorResponse']['Response']['ResponseStatusCode'];
			$returnerrlibelle = $arrayresponse['LocatorResponse']['Response']['ResponseStatusDescription'];
		}								
		if ( $returnerrcode != 1 ) {
			$errorws = __( ' ===> Search Access Points error - ', 'cdi' ) . 'UPS : ' . $returnerrcode . ' : ' . $returnerrlibelle;
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $errorws, 'tec' );
			$order->update_meta_data( '_cdi_meta_pickupLocationlabel', __( 'Non-existent point', 'cdi' ) . '=> Distance: ' );
			$order->update_meta_data( '_cdi_meta_pickupfulladdress', '' );
			$order->save();
		} else {
			// Parse each Point
			$dropLocations = $arrayresponse['LocatorResponse']['SearchResults']['DropLocation'];
			$returnrelays = array();
			foreach ( $dropLocations as $point ) {		
				// Select the $pickupLocationId
				if ( $pickupLocationId == $point['LocationID'] ) {
					$pointabstractlabel = cdi_c_Function::cdi_sanitize_voie( trim( $point['AddressKeyFormat']['ConsigneeName'] ) ) . ' =&gt; ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $point['AddressKeyFormat']['AddressLine'] ) ) . ' ' .
							  $point['AddressKeyFormat']['PostcodePrimaryLow'] . ' ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $point['AddressKeyFormat']['PoliticalDivision2'] ) ) . ' =&gt; Distance: ' .
							  '?' . 'm =&gt; Id: ' .
							  $pickupLocationId;
					$pointabstractlabel = htmlspecialchars_decode( $pointabstractlabel );
					$order->update_meta_data( '_cdi_meta_pickupLocationlabel', $pointabstractlabel );
					$pickupfulladdress = array();
					$pickupfulladdress['nom'] = substr( cdi_c_Function::cdi_sanitize_voie( trim( $point['AddressKeyFormat']['ConsigneeName'] ) ), 0, 35 );
					$pickupfulladdress['adresse1'] = substr( cdi_c_Function::cdi_sanitize_voie( trim( $point['AddressKeyFormat']['AddressLine'] ) ), 0, 35 );
					$pickupfulladdress['adresse2'] = '';
					$pickupfulladdress['adresse3'] = '';
					$pickupfulladdress['codePostal'] = preg_replace( '/[^a-zA-Z0-9\s]/', '', $point['AddressKeyFormat']['PostcodePrimaryLow'] );
					$pickupfulladdress['localite'] = substr( cdi_c_Function::cdi_sanitize_voie( trim( $point['AddressKeyFormat']['PoliticalDivision2'] ) ), 0, 30 );
					$pickupfulladdress['codePays'] = $point['AddressKeyFormat']['CountryCode'];
					$pickupfulladdress['libellePays'] = WC()->countries->countries[ $point['AddressKeyFormat']['CountryCode'] ];
					$order->update_meta_data( '_cdi_meta_pickupfulladdress', $pickupfulladdress );
					$errorws = 'ok';
					$order->save();
				}
			}
			if ( ! $errorws ) {
				$errorws = __( ' ===> Search Access Points error - ', 'cdi' ) . 'UPS Access Point ID not found : ' . $pickupLocationId;
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Error on change pickupid in admin : ' . $order_id . ' - ' . $pickupLocationId, 'msg' );
				$order->update_meta_data( '_cdi_meta_pickupLocationlabel', __( 'Non-existent point', 'cdi' ) . '=> Distance: ' );
				$order->update_meta_data( '_cdi_meta_pickupfulladdress', '' );
				$order->save();
			}
		}
	}

	/****************************************************************************************/
	/* CDI Parcel returns                                                                   */
	/****************************************************************************************/

	public static function cdi_prodlabel_parcelreturn( $id_order, $productcode ) {
		cdi_c_Ups_Retourcolis::cdi_ups_calc_parcelretour( $id_order, $productcode );
	}

	public static function cdi_isitopen_parcelreturn() {
		$return = get_option( 'cdi_o_settings_ups_parcelreturn' );
		return $return;
	}

	public static function cdi_isitvalidorder_parcelreturn( $id_order ) {
		$order = wc_get_order( $id_order );
		$cdi_tracking = $order->get_meta( '_cdi_meta_tracking' );
		if ( $cdi_tracking ) {
			return true;
		} else {
			return false;
		}
	}

	public static function cdi_text_inviteprint_parcelreturn() {
		$return = get_option( 'cdi_o_settings_ups_text_preceding_printreturn' );
		return $return;
	}

	public static function cdi_url_carrier_following_parcelreturn() {
		return null;
	}

	public static function cdi_whichproducttouse_parcelreturn( $shippingcountry ) {
		$productcode = '11';
		return $productcode;
	}

	public static function cdi_text_preceding_parcelreturn() {
		$return = get_option( 'cdi_o_settings_ups_text_preceding_parcelreturn' );
		return $return;
	}

	/****************************************************************************************/
	/* CDI fonctions                                                                        */
	/****************************************************************************************/

	public static function cdi_function_withoutsign_country( $country ) {
		return true;
	}

	public static function cdi_whereis_parcel( $order_id, $trackingcode ) {
		$errorws = null;	
		if ( $order_id and $trackingcode ) {
			$order = wc_get_order( $order_id );
			$order_date_obj = $order->get_date_created();
			$order_date = $order_date_obj->format( 'Y-m-d' );
			$limitdate = str_replace( '-', '', date( 'Y-m-d', strtotime( '-30 days' ) ) );
			$checkeddate = str_replace( '-', '', substr( $order_date, 0, 10 ) );
			$datetime1 = new DateTime( $limitdate );
			$datetime2 = new DateTime( $checkeddate );
			$difference = $datetime1->diff( $datetime2 );
			if ( $difference->invert > 0 ) {
				$errorws = '*** Plus de suivi au-dela de 30 jours.';
			} else {
				// Initiate structure
				include_once( 'ups-access-context.php' );
				// ****** Track Request
				if ( ! isset( $urltrack ) ) {
					return; // Case of rebound with include_once ('ups-access-context.php') not found
				}
							
				$headers = null ;
				$token = cdi_c_Ups_Get_Oauthcode::cdi_Ups_OAuth_get_token() ;
				if ($token) {
					//set headers with token
					$headers = [
						'Authorization: Bearer ' . $token,				    	
						'Content-Type: application/json',									
						'transId: CDI-' . $order_id . '-' . time(),
						'transactionSrc: CDI-Wordpress-WooCommerce',  
						];
				}
				// Create TrackRequest XMl
				$payload = array(
					"locale" => "fr_FR",
					"returnSignature" => "false",
					"returnMilestones" => "false",
					"returnPOD" => "false"
					);
/* Compressed log for CDI debug */ cdi_c_Function::cdi_debug( __LINE__, __FILE__, json_encode($payload), 'tec' );					
				$response = cdi_c_Function::cdi_url_post_remote( $urltrack . $trackingcode, $payload, $headers );
/* Compressed log for CDI debug */ cdi_c_Function::cdi_debug( __LINE__, __FILE__, $response, 'tec' );				
				$arrayresponse = json_decode( $response, true );	
				$returnerrcode = 1;
				$returnerrlibelle = null;
				if (isset( $arrayresponse['response']['errors'] )) {
					// Technical or Authentification error							
					$returnerrcode = $arrayresponse['response']['errors']['0']['code'];
					$returnerrlibelle = $arrayresponse['response']['errors']['0']['message'];
				}else{
					if (isset($arrayresponse['trackResponse']['Response']['ResponseStatus'])) {
						// API error									
						$returnerrcode = $arrayresponse['trackResponse']['Response']['ResponseStatus']['Code'];
						$returnerrlibelle = $arrayresponse['trackResponse']['Response']['ResponseStatus']['Description'];
					}
				}								
				if ( $returnerrcode != 1 ) {
					$errorws = __( ' ===> Error tracking : ', 'cdi' ) . $returnerrcode . ' : ' . $returnerrlibelle ;
				} else {
					$packages = $arrayresponse['trackResponse']['shipment']['package'];
					foreach ( $packages as $package ) {
						cdi_c_Function::cdi_debug( __LINE__, __FILE__, $package, 'tec' );
						if ( isset( $package['currentStatus']['description'] ) ) {
							$errorws = $package['currentStatus']['description'];
						}
					}
				}
			}
		}
		return $errorws;
	}

	public static function cdi_nochoicereturn_country( $country ) {
		return true;
	}

	/****************************************************************************************/
	/* CDI Bordereau de remise (dépôt)                                                              */
	/****************************************************************************************/

	public static function cdi_prod_remise_bordereau( $selected ) {
		$message = __( 'No delivery slip (deposit) for this carrier.', 'cdi' );
		return $message;
	}

	/****************************************************************************************/
	/* CDI Bordereaux Format                                                                */
	/****************************************************************************************/

	public static function cdi_prod_remise_format() {
		$format = get_option( 'cdi_o_settings_ups_OutputPrintingType' );
		return $format;
	}



}

?>
