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
/* Carrier Colissimo                                                                    */
/****************************************************************************************/

class cdi_c_Carrier_colissimo {
	public static function init() {
		if ( class_exists( 'SoapClient' ) ) {
			include_once dirname( __FILE__ ) . '/Colissimo-Affranchissement.php';
			cdi_c_Colissimo_Affranchissement::init();
			include_once dirname( __FILE__ ) . '/Colissimo-Remise.php';
			cdi_c_Colissimo_Remise_bordereau::init();
			include_once dirname( __FILE__ ) . '/Colissimo-Retourcolis.php';
			cdi_c_Colissimo_Retourcolis::init();
		}
	}

	/****************************************************************************************/
	/* Carrier Colissimo References Livraisons                                              */
	/****************************************************************************************/

	public static function cdi_carrier_update_settings() {
		$forcesettings = get_option( 'cdi_o_settings_colissimo_defautsettings' );
		if ( $forcesettings == 'yes' ) {
			$arrdefaut_colissimo_pickups = array( 'cdi_shipping_colissimo_pick1', 'cdi_shipping_colissimo_pick2' );
			$pickuplist = str_replace( ' ', '', get_option( 'cdi_o_settings_pickupmethodnames' ) );
			$arraypickuplist = explode( ',', $pickuplist );
			$arraypickuplist = array_map( 'trim', $arraypickuplist );
			foreach ( $arrdefaut_colissimo_pickups as $defaut_colissimo_pickup ) {
				if ( ! in_array( $defaut_colissimo_pickup, $arraypickuplist ) ) {
					$pickuplist = trim( $pickuplist, ' ,' );
					$pickuplist = $pickuplist . ',' . $defaut_colissimo_pickup;
					$pickuplist = str_replace( ',,', ',', $pickuplist );
					$pickuplist = trim( $pickuplist, ' ,' );
					update_option( 'cdi_o_settings_pickupmethodnames', $pickuplist );
				}
			}
			$arrdefaut_colissimo_products = array( 'cdi_shipping_colissimo_home1=DOM', 'cdi_shipping_colissimo_home2=DOS' );
			$productlist = str_replace( ' ', '', get_option( 'cdi_o_settings_forcedproductcodes' ) );
			$arrayproductlist = explode( ',', $productlist );
			$arrayproductlist = array_map( 'trim', $arrayproductlist );
			foreach ( $arrdefaut_colissimo_products as $defaut_colissimo_product ) {
				if ( ! in_array( $defaut_colissimo_product, $arrayproductlist ) ) {
					$productlist = trim( $productlist, ' ,' );
					$productlist = $productlist . ',' . $defaut_colissimo_product;
					$productlist = str_replace( ',,', ',', $productlist );
					$productlist = trim( $productlist, ' ,' );
					update_option( 'cdi_o_settings_forcedproductcodes', $productlist );
				}
			}

			$arrdefaut_colissimo_mandatoryphones = array( 'cdi_shipping_colissimo_pick1', 'cdi_shipping_colissimo_pick2' );
			$mandatoryphonelist = str_replace( ' ', '', get_option( 'cdi_o_settings_phonemandatory' ) );
			$arraymandatoryphonelist = explode( ',', $mandatoryphonelist );
			$arraymandatoryphonelist = array_map( 'trim', $arraymandatoryphonelist );
			foreach ( $arrdefaut_colissimo_mandatoryphones as $defaut_colissimo_mandatoryphone ) {
				if ( ! in_array( $defaut_colissimo_mandatoryphone, $arraymandatoryphonelist ) ) {
					$mandatoryphonelist = trim( $mandatoryphonelist, ' ,' );
					$mandatoryphonelist = $mandatoryphonelist . ',' . $defaut_colissimo_mandatoryphone;
					$mandatoryphonelist = str_replace( ',,', ',', $mandatoryphonelist );
					$mandatoryphonelist = trim( $mandatoryphonelist, ' ,' );
					update_option( 'cdi_o_settings_phonemandatory', $mandatoryphonelist );
				}
			}
		}
	}

	public static function cdi_isit_pickup_authorized() {
		global $woocommerce;
		$return = in_array( $woocommerce->customer->get_shipping_country(), explode( ',', get_option( 'cdi_o_settings_colissimo_InternationalPickupLocationContryCodes' ) ) );
		return $return;
	}

	public static function cdi_test_carrier() {
		// Test if server ssl and Colissimo Website are ok  - Only every 2 mn to avoid Colissimo servers
		global $msgtofrontend;
		$currenttimer = time();
		$oldtimercolissimo = get_option( 'cdi_o_testcarriercolissimo' );
		if ( ! $oldtimercolissimo or ( $currenttimer > ( $oldtimercolissimo + 120 ) ) ) {
			update_option( 'cdi_o_testcarriercolissimo', $currenttimer );
			$urlsupervision = 'http://ws.colissimo.fr/supervision-wspudo/supervision.jsp';
			$etat = cdi_c_Function::cdi_url_post_remote( $urlsupervision );
			if ( ! strpos( 'x' . $etat, '[OK]' ) > 0 ) {
				$msgtofrontend = ' CDI : Colissimo urlsupervision access denied.';
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Colissimo urlsupervision access denied.', 'tec' );
				$return = false;
			} else {
				$return = true;
			}
			update_option( 'cdi_o_testcarriercolissimo', $currenttimer );
			update_option( 'cdi_o_testcarriercolissimoresult', $return );

		} else {
			$return = get_option( 'cdi_o_testcarriercolissimoresult' );
		}
		return $return;
	}

	public static function cdi_get_points_livraison( $relaytype ) {
		global $woocommerce;
		global $msgtofrontend;

		if ( self::cdi_test_carrier() === false ) {
			return false;
		}
		
		$accountNumber =  get_option( 'cdi_o_settings_colissimo_contractnumber' )  ;
		$password = get_option( 'cdi_o_settings_colissimo_password' ) ;
		$address = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_string( cdi_c_Function::cdi_sanitize_accents( $woocommerce->customer->get_shipping_address() ) ) );
		$zipCode = $woocommerce->customer->get_shipping_postcode() ;
		$city = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_string( cdi_c_Function::cdi_sanitize_accents( $woocommerce->customer->get_shipping_city() ) ) );
		$countryCode = $woocommerce->customer->get_shipping_country() ;
		$weightrelay = (float) $woocommerce->cart->cart_contents_weight;
		if ( get_option( 'woocommerce_weight_unit' ) == 'kg' ) { // Convert kg to g
			$weightrelay = $weightrelay * 1000;
		}
		$weightrelay = round( $weightrelay + get_option( 'cdi_o_settings_parceltareweight' ) );
		if ( ! $weightrelay or $weightrelay == 0 ) {
			$weightrelay = 100; // 0g is not good but 1g would be enought to not break the Colissimo WS
		}
		$calc = get_option( 'cdi_o_settings_colissimo_OffsetDepositDate' );
		$shippingDate = date( 'd/m/Y', strtotime( "+$calc day" ) ) ;
		$filterRelay = $relaytype ;
		$requestId = 'CDI-' . date( 'YmdHis' ) ;
		$optionInter = '1' ;
		
		$params = 'https://ws.colissimo.fr/pointretrait-ws-cxf/PointRetraitServiceWS/2.0/findRDVPointRetraitAcheminement'
			. '?accountNumber=' . $accountNumber 
			. '&password=' . $password 
			. '&address=' . $address
			. '&zipCode=' . $zipCode
			. '&city=' . $city			
			. '&countryCode=' . $countryCode
			. '&weightrelay=' . $weightrelay			
			. '&shippingDate=' . $shippingDate			
			. '&filterRelay=' . $filterRelay			
			. '&requestId=' . $requestId			
			. '&optionInter=' . $optionInter ;						
		$result = cdi_c_Function::cdi_url_get_remote( $params);
		if ($result) {
                	$codehttp = $result['response']['code'] ;
		}else{
			$codehttp = '' ;
		}
		if ($codehttp == 200) {
			// Request has been received by server
			$ok = '<ok><return>' . cdi_c_Reference_Livraisons::get_string_between( $result['body'], '<return>', '</return>' ) . '</return></ok>' ;
			// Remplace empty data by 'nulltag' string so to not be considered as object
			$ok = preg_replace( '#(<[a-zA-Z0-9]+>)(</[a-zA-Z0-9]+>)#', '$1nulltag$2', $ok);

			// Convert to object stdClass												
			$ok = simplexml_load_string($ok, $class_name = null, LIBXML_NOBLANKS);			
			$ok = json_encode($ok) ;
			$ok = (object) json_decode($ok, false);
			
			$retid = $ok->return->errorCode;
			$retmessageContent = $ok->return->errorMessage;
			if ( $retid == 0 ) {
			        // Replace 'nulltag' by a php null
				$listePointRetraitAcheminement = $ok->return->listePointRetraitAcheminement ;			
				foreach ($listePointRetraitAcheminement as $num=>$pointretrait) {						
					foreach ($pointretrait as $attr=>$value) {
  						if ($value == 'nulltag') {
							$listePointRetraitAcheminement[$num]->$attr = null ;
						}
					}
				}						
				$return = $ok;
			} else {
				// process the error from soap server
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, (string) $retid, 'exp' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, (string) $retmessageContent, 'exp' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, (string) $params, 'exp' );
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, $ok, 'exp' );
				$msgtofrontend = ' (' . $retid . ' - ' . $retmessageContent . ')';
				$return = false;
			}			
		}else{
			// process the error http 
                	$messagehttp = $result['response']['message'] ;	
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $codehttp . ' - ' . $messagehttp, 'exp' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $params, 'exp' );			
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $result['body'], 'exp' );
			$return = false;
		}
		return $return;
	}

	public static function cdi_check_pickup_and_location() {
		// Check pickup product code but no pickup location
		$codeproductfound = WC()->session->get( 'cdi_forcedproductcode' );
		$cdipickuplocationid = WC()->session->get( 'cdi_pickuplocationid' );
		if ( in_array( $codeproductfound, array( 'BPR', 'ACP', 'CDI', 'BDP', 'A2P', 'CMT', 'PCS' ) ) and empty( $cdipickuplocationid ) ) { // error to catch
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $codeproductfound, 'tec' );
			throw new Exception( __( 'Pickup location - Technical error on pickup product code vs location id. Please try again.', 'cdi' ) );
		}
	}

	/****************************************************************************************/
	/* Carrier Colissimo Front-end tracking                                            */
	/****************************************************************************************/

	public static function cdi_text_preceding_trackingcode() {
		$return = __( get_option( 'cdi_o_settings_colissimo_text_preceding_trackingcode' ), 'cdi' );
		return $return;
	}

	public static function cdi_url_trackingcode() {
		$return = get_option( 'cdi_o_settings_colissimo_url_trackingcode' );
		return $return;
	}

	/****************************************************************************************/
	/* CDI Meta box in order panel                                                          */
	/****************************************************************************************/

	public static function cdi_metabox_initforcarrier( $order_id, $order ) {
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
		<?php
	}

	public static function cdi_metabox_optional_choices( $order_id, $order ) {
		?>
		<?php $shipping_country = $order->get_shipping_country(); ?>
		<div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Optional services', 'cdi' ); ?></div><p style="clear:both"></p>

		<?php if ( ! self::cdi_function_withoutsign_country( $shipping_country ) ) { ?>
			<?php $order->update_meta_data( '_cdi_meta_signature', 'yes' ); ?>
			<?php $order->save(); ?>
		<?php } ?>

		<p style='width:50%; float:left;  margin-top:0px;'><a><?php _e( 'Signature : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_signature',
																				'type' => 'text',
																				'options' => array(
																					'yes' => __( 'yes', 'cdi' ),
																					'no' => __( 'no', 'cdi' ),
																				),
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_signature',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>

		  <?php
			if ( $order->get_meta( '_cdi_meta_signature' ) == 'yes' ) {
				?>
				 <!--  Additionnal insurance display --> 
		<p style='width:50%; float:left;  margin-top:0px;'><a><?php _e( 'Compensation + : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_additionalcompensation',
																				'type' => 'text',
																				'options' => array(
																					'yes' => __( 'yes', 'cdi' ),
																					'no' => __( 'no', 'cdi' ),
																				),
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_additionalcompensation',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>

				  <?php
					if ( $order->get_meta( '_cdi_meta_additionalcompensation' ) == 'yes' ) {
						?>
					 <!--  Amount compensation display --> 
		<p style='width:30%; float:left; margin-left:20%; margin-top:5px;'><a><?php _e( 'Amount : ', 'cdi' ); ?>
																						<?php
																						woocommerce_wp_text_input(
																							array(
																								'name' => '_cdi_meta_amountcompensation',
																								'type' => 'text',
																								'data_type' => 'decimal',
																								'style' => 'width:45%; float:left;',
																								'id'   => '_cdi_meta_amountcompensation',
																								'label' => '',
																							), $order,
																						);
																						?>
			 </a></p><p style="clear:both"></p>

			<?php } ?> <!--  End Amount compensation display --> 

		  <?php } ?> <!--  End Additionnal insurance display display --> 

		<!--Option Avis réception --> 
		<p style='width:50%; float:left;  margin-top:5px;'><a>Avis réception
		<?php
		woocommerce_wp_select(
			array(
				'name' => '_cdi_meta_returnReceipt',
				'type' => 'text',
				'options' => array(
					'non' => __( 'sans', 'cdi' ),
					'oui' => __( 'avec', 'cdi' ),
				),
				'style' => 'width:45%; float:left;',
				'id'   => '_cdi_meta_returnReceipt',
				'label' => '',
			), $order,
		);
		?>
			 </a></p><p style="clear:both"></p>
		<!--  Fin Option Avis réception --> 

		  <?php
			if ( self::cdi_nochoicereturn_country( $shipping_country ) == true ) {
				?>
				 <!--  Return internationnal display --> 
		<p style='width:50%; float:left;  margin-top:5px;'><a><?php _e( 'Return : ', 'cdi' ); ?>
																		<?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_typereturn',
																				'type' => 'text',
																				'options' => array(
																					'no-return'      => __( 'No return', 'cdi' ),
																					'pay-for-return' => __( 'Pay for return', 'cdi' ),
																				),
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_typereturn',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>
		  <?php } ?> <!--  End Return internationnal display --> 

		  <?php
			if ( cdi_c_Function::cdi_outremer_country_ftd( $shipping_country ) == true ) {
				?>
				 <!--Option franc taxes-douanes --> 
		<p style='width:50%; float:left;  margin-top:5px;'><a>ftd OM
				<?php
				woocommerce_wp_select(
					array(
						'name' => '_cdi_meta_ftd',
						'type' => 'text',
						'options' => array(
							'non' => __( 'non ftd', 'cdi' ),
							'oui' => __( 'en ftd', 'cdi' ),
						),
						'style' => 'width:45%; float:left;',
						'id'   => '_cdi_meta_ftd',
						'label' => '',
					), $order,
				);
				?>
			 </a></p><p style="clear:both"></p>
		  <?php } ?> <!--  Fin Option franc de taxes-douanes --> 
		<?php
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
																					'DOM' => __( 'DOM - EU Domicile sans signature', 'cdi' ),
																					'DOS' => __( 'DOS - EU Domicile avec signature', 'cdi' ),
																					'COM' => __( 'COM - OM Domicile sans signature', 'cdi' ),
																					'CDS' => __( 'CDS - OM Domicile avec signature', 'cdi' ),
																					'COLI' => __( 'COLI - IN Domicile', 'cdi' ),
																					'A2P' => __( 'A2P - FR Point retrait', 'cdi' ),
																					'BPR' => __( 'BPR - FR Retrait La Poste', 'cdi' ),
																					'CMT' => __( 'CMT - EU Point retrait', 'cdi' ),
																					'BDP' => __( 'BDP - EU Retrait La Poste', 'cdi' ),
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
				<p><a><?php _e( 'Location : ', 'cdi' ); ?></a><a style='color:black'><?php echo esc_attr( $pickupLocationlabel ); ?> </a></p>
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
		  <div style='background-color:#eeeeee; color:#000000; width:100%; height:8px; font-size:smaller; line-height:8px;'><?php _e( 'Article : ', 'cdi' ); ?><?php echo $nbart; ?></div><p style="clear:both"></p>
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
		if ( get_option( 'cdi_o_settings_colissimo_parcelreturn' ) == 'yes' ) {
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
 
				<?php $cdi_urllabel_return = $order->get_meta( '_cdi_meta_pdfurl_return' ); ?>
			  <p><a style="display:inline-block;"><?php _e( 'To return label : ', 'cdi' ); ?></a><a style="vertical-align: middle; display:inline-block; color:black; width:12em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" href="<?php echo esc_url( $cdi_urllabel_return ); ?>" onclick="window.open(this.href); return false;" > <?php echo esc_url( $cdi_urllabel_return ); ?> </a></p>

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
		$contractnumber = get_option( 'cdi_o_settings_colissimo_contractnumber' );
		$password = get_option( 'cdi_o_settings_colissimo_password' );
		$calc = get_option( 'cdi_o_settings_colissimo_OffsetDepositDate' );
		$date = ( date( 'd/m/Y', strtotime( "+$calc day" ) ) );
		$result = cdi_c_Function::cdi_url_get_remote( 'https://ws.colissimo.fr/pointretrait-ws-cxf/PointRetraitServiceWS/2.0/findPointRetraitAcheminementByID?accountNumber=' . $contractnumber . '&password=' . $password . '&id=' . $pickupLocationId . '&date=' . $date );
		if ( !$result ) {
			return;
		}
		$result = $result['body'];
		$response = '<pointRetraitAcheminement>' . cdi_c_Reference_Livraisons::get_string_between( $result, '<pointRetraitAcheminement>', '</pointRetraitAcheminement>' ) . '</pointRetraitAcheminement>';
		$erreur = cdi_c_Reference_Livraisons::get_string_between( $result, '<errorCode>', '</errorCode>' );
		$arrayresponse = json_decode( json_encode( (array) simplexml_load_string( $response ) ), true );
		if ( $erreur != 0 or ! $arrayresponse ) {
			$errorws = __( ' ===> Search Access Points error - ', 'cdi' ) . 'Colissimo : ' . $erreur . ' : ' . cdi_c_Reference_Livraisons::get_string_between( $result, '<errorMessage>', '</errorMessage>' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, $errorws, 'tec' );
			$order->update_meta_data( '_cdi_meta_pickupLocationlabel', __( 'Non-existent point', 'cdi' ) . '=> Distance: ' );
			$order->update_meta_data( '_cdi_meta_pickupfulladdress', '' );
		} else {
			$pointabstractlabel = cdi_c_Function::cdi_sanitize_voie( trim( $arrayresponse['nom'] ) ) . ' =&gt; ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $arrayresponse['adresse1'] ) ) . ' ' .
							  $arrayresponse['codePostal'] . ' ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $arrayresponse['localite'] ) ) . ' =&gt; Distance: ' .
							  '?' . 'm =&gt; Id: ' .
							  $pickupLocationId;
			$pointabstractlabel = htmlspecialchars_decode( $pointabstractlabel );
			$order->update_meta_data( '_cdi_meta_pickupLocationlabel', $pointabstractlabel );
			$pickupfulladdress = array();
			$pickupfulladdress['nom'] = cdi_c_Function::cdi_sanitize_voie( trim( $arrayresponse['nom'] ) );
			$pickupfulladdress['adresse1'] = cdi_c_Function::cdi_sanitize_voie( trim( $arrayresponse['adresse1'] ) );
			$pickupfulladdress['adresse2'] = '';
			$pickupfulladdress['adresse3'] = '';
			$pickupfulladdress['codePostal'] = preg_replace( '/[^a-zA-Z0-9\s]/', '', $arrayresponse['codePostal'] );
			$pickupfulladdress['localite'] = cdi_c_Function::cdi_sanitize_voie( trim( $arrayresponse['localite'] ) );
			$pickupfulladdress['codePays'] = $arrayresponse['codePays'];
			$pickupfulladdress['libellePays'] = WC()->countries->countries[ $arrayresponse['codePays'] ];
			$order->update_meta_data( '_cdi_meta_pickupfulladdress', $pickupfulladdress );
		}
		$order->save();
	}

	/****************************************************************************************/
	/* CDI Parcel returns                                                                   */
	/****************************************************************************************/

	public static function cdi_prodlabel_parcelreturn( $id_order, $productcode ) {
		cdi_c_Colissimo_Retourcolis::cdi_colissimo_calc_parcelretour( $id_order, $productcode );
	}

	public static function cdi_isitopen_parcelreturn() {
		$return = get_option( 'cdi_o_settings_colissimo_parcelreturn' );
		return $return;
	}

	public static function cdi_isitvalidorder_parcelreturn( $id_order ) {
		$order = wc_get_order( $id_order );
		$cdi_tracking = $order->get_meta( '_cdi_meta_tracking' );
		$cdi_tracking_heading = substr( $cdi_tracking, 0, 2 );
		$trackingheaders_parcelreturn = get_option( 'cdi_o_settings_colissimo_trackingheaders_parcelreturn' );
		if ( ! ( strpos( $trackingheaders_parcelreturn, $cdi_tracking_heading ) === false ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function cdi_text_inviteprint_parcelreturn() {
		$return = get_option( 'cdi_o_settings_colissimo_text_preceding_printreturn' );
		return $return;
	}

	public static function cdi_url_carrier_following_parcelreturn() {
		$return = get_option( 'cdi_o_settings_colissimo_url_following_printreturn' );
		return $return;
	}

	public static function cdi_whichproducttouse_parcelreturn( $shippingcountry ) {
		$productcode = '';
		$arrcoderelationlist = explode( ';', get_option( 'cdi_o_settings_colissimo_returnproduct_code' ) );
		foreach ( $arrcoderelationlist as $coderelationlist ) {
			$arrcodereturn = explode( '=', $coderelationlist );
			if ( ! ( strpos( $arrcodereturn[1], $shippingcountry ) === false ) ) {
				$productcode = $arrcodereturn[0];
				break;
			}
		}
		return $productcode;
	}

	public static function cdi_text_preceding_parcelreturn() {
		$return = get_option( 'cdi_o_settings_colissimo_text_preceding_parcelreturn' );
		return $return;
	}



	/****************************************************************************************/
	/* CDI fonctions                                                                        */
	/****************************************************************************************/

	public static function cdi_function_withoutsign_country( $country ) {
		$arr_country_without_sign = get_option( 'cdi_o_settings_colissimo_InternationalWithoutSignContryCodes' );
		if ( $country and strpos( 'XX,' . $arr_country_without_sign, $country ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	public static function cdi_whereis_parcel( $order_id, $trackingcode ) {
		$order = wc_get_order( $order_id );
		$arrayinovert = $order->get_meta( 'cdi_colis_inovert' );
		if ( $arrayinovert && strpos( 'LIV', $arrayinovert['eventCode'] ) ) { // Livré, pour toute raison inovert
			$msgsuivicolis = ' ' . $arrayinovert['eventCode'] . ' ' . $arrayinovert['eventDate'] . ' | ' . $arrayinovert['eventLibelle'] . ' | ' . $arrayinovert['eventSite'] . ' ' . $arrayinovert['recipientCity'] . ' ' . $arrayinovert['recipientCountryCode'] . ' ' . $arrayinovert['recipientZipCode'];
			return $msgsuivicolis;
		} else {
			$order = wc_get_order( $order_id );
			$order_date_obj = $order->get_date_created();
			$order_date = $order_date_obj->format( 'Y-m-d' );
			$limitdate = str_replace( '-', '', date( 'Y-m-d', strtotime( '-30 days' ) ) );
			$checkeddate = str_replace( '-', '', substr( $order_date, 0, 10 ) );
			$datetime1 = new DateTime( $limitdate );
			$datetime2 = new DateTime( $checkeddate );
			$difference = $datetime1->diff( $datetime2 );
			if ( $difference->invert > 0 ) {
				return '*** Plus de suivi au-dela de 30 jours.';
			} else {
				// Initiate structure
				$cdicolcontractnumber = get_option( 'cdi_o_settings_colissimo_contractnumber' );
				$cdicolpassword = get_option( 'cdi_o_settings_colissimo_password' );
				// $cdicolcontractnumber = '123456' ; // for Colissimo account test
				// $cdicolpassword = 'ABC123' ;  // for Colissimo account test
				$result = cdi_c_Function::cdi_url_get_remote( 'https://www.coliposte.fr/tracking-chargeur-cxf/TrackingServiceWS/track?accountNumber=' . $cdicolcontractnumber . '&password=' . $cdicolpassword . '&skybillNumber=' . $trackingcode );
				if ( !$result ) {
					return '*** Erreur suivi. ';
				}
				$result = $result['body'];
				$errorCode = cdi_c_Function::get_string_between( $result, '<errorCode>', '</errorCode>' );
				if ( $errorCode !== '0' ) {
					return '*** Erreur suivi : ' . $errorCode;
				} else {
					$arrayinovert = array();
					$arrayinovert['eventCode'] = cdi_c_Function::get_string_between( $result, '<eventCode>', '</eventCode>' );
					$arrayinovert['eventDate'] = cdi_c_Function::get_string_between( $result, '<eventDate>', '</eventDate>' );
					$arrayinovert['eventLibelle'] = cdi_c_Function::get_string_between( $result, '<eventLibelle>', '</eventLibelle>' );
					$arrayinovert['eventSite'] = cdi_c_Function::get_string_between( $result, '<eventSite>', '</eventSite>' );
					$arrayinovert['recipientCity'] = cdi_c_Function::get_string_between( $result, '<recipientCity>', '</recipientCity>' );
					$arrayinovert['recipientCountryCode'] = cdi_c_Function::get_string_between( $result, '<recipientCountryCode>', '</recipientCountryCode>' );
					$arrayinovert['recipientZipCode'] = cdi_c_Function::get_string_between( $result, '<recipientZipCode>', '</recipientZipCode>' );
					$order->update_meta_data( 'cdi_colis_inovert', $arrayinovert );
					$msgsuivicolis = ' ' . $arrayinovert['eventCode'] . ' ' . $arrayinovert['eventDate'] . ' | ' . $arrayinovert['eventLibelle'] . ' | ' . $arrayinovert['eventSite'] . ' ' . $arrayinovert['recipientCity'] . ' ' . $arrayinovert['recipientCountryCode'] . ' ' . $arrayinovert['recipientZipCode'];
					return $msgsuivicolis;
				}
			}
		}
	}

	public static function cdi_nochoicereturn_country( $country ) {
		$country_parcelreturn = get_option( 'cdi_o_settings_colissimo_nochoicereturn_country' );
		if ( ! $country_parcelreturn ) {
			$country_parcelreturn = 'US,AU,JP,DE,AT,BE,BG,CY,DK,ES,EE,FI,FR,GR,HU,IE,IT,LV,LT,LU,MT,NL,PL,PT,CZ,RO,GB,IE,SK,SI,SE'; // For Back compatibility
		}
		$array_country_parcelreturn = explode( ',', $country_parcelreturn );
		if ( ! in_array( $country, $array_country_parcelreturn ) ) {
			return true;
		} else {
			return false;
		}
	}

	/****************************************************************************************/
	/* CDI Bordereau de remise (dépôt)                                                      */
	/****************************************************************************************/

	public static function cdi_prod_remise_bordereau( $selected ) {
		$message = cdi_c_Colissimo_Remise_bordereau::cdi_colissimo_prod_remise_bordereau( $selected );
		return $message;
	}

	/****************************************************************************************/
	/* CDI Bordereaux Format                                                                */
	/****************************************************************************************/

	public static function cdi_prod_remise_format() {
		$format = get_option( 'cdi_o_settings_colissimo_OutputPrintingType' );
		return $format;
	}


}

?>
