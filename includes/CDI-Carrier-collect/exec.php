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
/* Carrier collect                                                                       */
/****************************************************************************************/

class cdi_c_Carrier_collect {
	public static function init() {
		include_once dirname( __FILE__ ) . '/Collect-Affranchissement.php';
		cdi_c_Collect_Affranchissement::init();
		include_once dirname( __FILE__ ) . '/Collect-Follow.php';
		cdi_c_Collect_Follow::init();
	}

	/****************************************************************************************/
	/* Carrier References Livraisons                                                        */
	/****************************************************************************************/

	public static function cdi_carrier_update_settings() {
		$forcesettings = get_option( 'cdi_o_settings_collect_defautsettings' );
		if ( $forcesettings == 'yes' ) {
			$arrdefaut_collect_pickcollect = array( 'cdi_shipping_collect_pick1', 'cdi_shipping_collect_pick2' );
			$pickuplist = str_replace( ' ', '', get_option( 'cdi_o_settings_pickupmethodnames' ) );
			$arraypickuplist = explode( ',', $pickuplist );
			$arraypickuplist = array_map( 'trim', $arraypickuplist );
			foreach ( $arrdefaut_collect_pickcollect as $defaut_collect_pickup ) {
				if ( ! in_array( $defaut_collect_pickup, $arraypickuplist ) ) {
					$pickuplist = trim( $pickuplist, ' ,' );
					$pickuplist = $pickuplist . ',' . $defaut_collect_pickup;
					$pickuplist = str_replace( ',,', ',', $pickuplist );
					$pickuplist = trim( $pickuplist, ' ,' );
					update_option( 'cdi_o_settings_pickupmethodnames', $pickuplist );
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

		function distance( $lat1, $lng1, $lat2, $lng2 ) {
			if ( ! $lat1 or ! $lng1 or ! $lat2 or ! $lng2 ) {
				return '9999';
			}
			$pi80 = M_PI / 180;
			$lat1 *= $pi80;
			$lng1 *= $pi80;
			$lat2 *= $pi80;
			$lng2 *= $pi80;
			$r = 6372.797; // rayon moyen de la Terre en km
			$dlat = $lat2 - $lat1;
			$dlng = $lng2 - $lng1;
			$a = sin( $dlat / 2 ) * sin( $dlat / 2 ) + cos( $lat1 ) * cos( $lat2 ) * sin( $dlng / 2 ) * sin( $dlng / 2 );
			$c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
			$km = $r * $c;
			return $km * 1000;
		}

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

		$arraccesspoints = get_option( 'cdi_o_settings_collect_pointslist' );
		// process the return
		$returnrelays = array();
		foreach ( $arraccesspoints as $point ) {
			if ( $point['lat'] and $point['lon'] ) {
				$latpoint = $point['lat'];
				$lonpoint = $point['lon'];
			} else {
				// Calc Point GPS
				$add = cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_string( cdi_c_Function::cdi_sanitize_accents( $point['adl1'] . ' ' . $point['adl2'] . ' ' . $point['adl3'] ) ) ) . ', '
				. $point['adcp'] . ' '
				. cdi_c_Function::cdi_sanitize_voie( cdi_c_Function::cdi_sanitize_string( cdi_c_Function::cdi_sanitize_accents( $point['adcity'] ) ) ) . ', '
				. WC()->countries->countries[ $point['adcodcountry'] ];
				$add = preg_replace( '/\s+/', ' ', $add );
				$postdata = array(
					'q' => $add,
					'format' => 'json',
				);
				$result = cdi_c_Function::cdi_url_get_remote( 'https://nominatim.openstreetmap.org/search?' . http_build_query( $postdata ) );
				$result = $result['body'] ;
				$latpoint = substr( cdi_c_Reference_Livraisons::get_string_between( $result, '"lat":"', '"' ), 0, 9 );
				$lonpoint = substr( cdi_c_Reference_Livraisons::get_string_between( $result, '"lon":"', '"' ), 0, 9 );
			}
			if ( ! $lat or ! $lon ) {
				$distance = '9999';
			} else {
				// Calc distance between 2 GPS points
				$distance = round( distance( $lat, $lon, $latpoint, $lonpoint ) );
			}
			$arr = array(
				'identifiant' => ltrim( $point['id'] ),
				'nom' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['name'] ) ),
				'adresse1' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['adl1'] ) ),
				'adresse2' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['adl2'] ) ),
				'adresse3' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['adl3'] ) ),
				'codePostal' => preg_replace( '/[^a-zA-Z0-9\s]/', '', $point['adcp'] ),
				'localite' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['adcity'] ) ),
				'codePays' => $point['adcodcountry'],
				'libellePays' => WC()->countries->countries[ $point['adcodcountry'] ],
				'indiceDeLocalisation' => rtrim( cdi_c_Function::cdi_sanitize_voie( $point['indice'] ) ),
				'langue' => 'FR',
				'phone' => $point['phone'],
				'parking' => $point['parking'],
				'loanOfHandlingTool' => null,
				'reseau' => 'xx',
				'coordGeolocalisationLatitude' => (float) str_replace( ',', '.', $latpoint ),
				'coordGeolocalisationLongitude' => (float) str_replace( ',', '.', $lonpoint ),
				'distanceEnMetre' => $distance,

				'horairesOuvertureLundi' => $point['horomon'],
				'horairesOuvertureMardi' => $point['horotue'],
				'horairesOuvertureMercredi' => $point['horowed'],
				'horairesOuvertureJeudi' => $point['horothu'],
				'horairesOuvertureVendredi' => $point['horofri'],
				'horairesOuvertureSamedi' => $point['horosat'],
				'horairesOuvertureDimanche' => $point['horosun'],
				'poidsMaxi' => '30000',
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
		// cdi_c_Function::cdi_debug(__LINE__ ,__FILE__ , $PointsRelais, 'tec');
		return $return;
	}

	public static function cdi_check_pickup_and_location() {
	}

	/****************************************************************************************/
	/* Carrier Front-end tracking                                                           */
	/****************************************************************************************/

	public static function cdi_text_preceding_trackingcode() {
		$return = __( get_option( 'cdi_o_settings_collect_text_preceding_trackingcode' ), 'cdi' );
		return $return;
	}

	public static function cdi_url_trackingcode() {
		$return = admin_url( 'admin-ajax.php' ) . '?action=cdi_collect_follow&trk=';
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

		  <?php $cdi_urllabel = $order->get_meta( '_cdi_meta_urllabel' ); ?>
		  <?php if ( $cdi_urllabel ) { ?> 
				<p><a style="display:inline-block;"><?php _e( 'To Labels :  ', 'cdi' ); ?></a><a style="vertical-align: middle; display:inline-block; color:black; width:12em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" href="<?php echo esc_url( $cdi_urllabel ); ?>" onclick="window.open(this.href); return false;" > <?php echo esc_url( $cdi_urllabel ); ?> </a></p>
		<?php } ?>

		  <?php if ( $order->get_meta( '_cdi_meta_exist_uploads_label' ) == true ) { ?>
		  <p style="display:inline-block; margin:0px;">
		<?php } ?>

			 <?php if ( $order->get_meta( '_cdi_meta_exist_uploads_label' ) == true ) { ?>
					 <form method="post" id="cdi_local_label_pdf" action="" style="display:inline-block;">
					  <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
					  <input type="submit" name="cdi_local_label_pdf" value="Print label"  title="Print label" /> 
					  <?php wp_nonce_field( 'cdi_local_label_pdf', 'cdi_local_label_pdf_nonce' ); ?> 
					</form>
		   <?php } ?>

		</p>

		<p style='width:35%; float:left; margin-top:5px;'><a><?php _e( 'Click&Collect : ', 'cdi' ); ?>
																	   <?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_collect_status',
																				'type' => 'text',
																				'options' => array(
																					'preparation' => __( 'In preparation for', 'cdi' ),
																					'atcollectpoint' => __( 'At collect point', 'cdi' ),
																					'courier' => __( 'Courier is running', 'cdi' ),																					
																					'delivered' => __( 'Delivered to customer', 'cdi' ),
																					'customeragreement' => __( 'Customer agreement', 'cdi' ),
																					
																				),
																				'style' => 'width:60%; float:left;',
																				'id'   => '_cdi_meta_collect_status',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>  
			
		<p style='width:35%; float:left; margin-top:5px;'><a><?php _e( 'Security : ', 'cdi' ); ?>
																	   <?php
																		woocommerce_wp_select(
																			array(
																				'name' => '_cdi_meta_securitymode',
																				'type' => 'text',
																				'options' => array(
																					'applysecurity' => __( 'With security codes to apply', 'cdi' ),
																					'free' => __( 'Without security code', 'cdi' ),
																				),
																				'style' => 'width:60%; float:left;',
																				'id'   => '_cdi_meta_securitymode',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>
			 
		<p style='width:50%; float:left; margin-top:5px;'><a><?php _e( 'Delivered code : ', 'cdi' ); ?>
																	   <?php
																		woocommerce_wp_text_input(
																			array(
																				'name' => '_cdi_meta_deliveredcode',
																				'type' => 'text',
																				'style' => 'width:45%; float:left;',
																				'id'   => '_cdi_meta_deliveredcode',
																				'label' => '',
																			), $order,
																		);
																		?>
			 </a></p><p style="clear:both"></p>
			 </a></p><p style="clear:both"></p>

		<?php
	}

	public static function cdi_metabox_parcel_settings( $order_id, $order ) {
	}

	public static function cdi_metabox_optional_choices( $order_id, $order ) {
	}

	public static function cdi_metabox_shipping_customer_choices( $order_id, $order ) {
		?>
		<!--  Pickup location web services - can be filled by meta box or retraitpoint web services --> 
		<div style='background-color:#eeeeee; color:#000000; width:100%;'><?php _e( 'Customer shipping settings :', 'cdi' ); ?></div><p style="clear:both"></p>
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
	}

	public static function cdi_metabox_parcel_return( $order_id, $order ) {
	}

	public static function cdi_metabox_shipping_updatepickupaddress( $order_id, $order ) {
		global $woocommerce;
		$pickupLocationId = $order->get_meta( '_cdi_meta_pickupLocationId' );
		$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $order_id );
		$collectpointid = $array_for_carrier['pickup_Location_id'];
		$arraccesspoints = get_option( 'cdi_o_settings_collect_pointslist' );
		if ( $arraccesspoints and count( $arraccesspoints ) !== 0 ) {
			foreach ( $arraccesspoints as $collectpoint ) {
				if ( $collectpoint['id'] == $collectpointid ) {
					$pointabstractlabel = cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['name'] ) ) . ' =&gt; ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['adl1'] ) ) . ' ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['adl2'] ) ) . ' ' .
							  $collectpoint['adcp'] . ' ' .
							  cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['adcity'] ) ) . ' =&gt; Distance: ' .
							  '?' . 'm =&gt; Id: ' .
							  $collectpoint['id'];
					$pointabstractlabel = htmlspecialchars_decode( $pointabstractlabel );
					$order->update_meta_data( '_cdi_meta_pickupLocationlabel', $pointabstractlabel );
					$pickupfulladdress = array();
					$pickupfulladdress['nom'] = cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['name'] ) );
					$pickupfulladdress['adresse1'] = cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['adl1'] ) );
					$pickupfulladdress['adresse2'] = cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['adl2'] ) );
					$pickupfulladdress['adresse3'] = cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['adl3'] ) );
					$pickupfulladdress['codePostal'] = preg_replace( '/[^a-zA-Z0-9\s]/', '', $collectpoint['adcp'] );
					$pickupfulladdress['localite'] = cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['adcity'] ) );
					$pickupfulladdress['codePays'] = $collectpoint['adcodcountry'];
					$pickupfulladdress['libellePays'] = WC()->countries->countries[ $collectpoint['adcodcountry'] ];
					$pickupfulladdress['phone'] = cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['phone'] ) );
					$pickupfulladdress['parking'] = cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['parking'] ) );
					$pickupfulladdress['indice'] = cdi_c_Function::cdi_sanitize_voie( trim( $collectpoint['indice'] ) );
					$order->update_meta_data( '_cdi_meta_pickupfulladdress', $pickupfulladdress );
					$order->save();
					return;
				}
			}
		}
		$order->update_meta_data( '_cdi_meta_pickupLocationlabel', __( 'Non-existent point', 'cdi' ) . '=> Distance: ' );
		$order->update_meta_data( '_cdi_meta_pickupfulladdress', '' );
		$order->save();
		cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Error on change pickupid in admin : ' . $order_id . ' - ' . $pickupLocationId, 'msg' );
		return;
	}

	/****************************************************************************************/
	/* CDI Parcel returns                                                                   */
	/****************************************************************************************/

	public static function cdi_prodlabel_parcelreturn( $id_order, $productcode ) {
	}

	public static function cdi_isitopen_parcelreturn() {
		return false;
	}

	public static function cdi_isitvalidorder_parcelreturn() {
		return null;
	}

	public static function cdi_text_inviteprint_parcelreturn() {
		return null;
	}

	public static function cdi_url_carrier_following_parcelreturn() {
		return null;
	}

	public static function cdi_whichproducttouse_parcelreturn( $shippingcountry ) {
		return null;
	}

	public static function cdi_text_preceding_parcelreturn() {
		return null;
	}

	/****************************************************************************************/
	/* CDI fonctions                                                                        */
	/****************************************************************************************/

	public static function cdi_function_withoutsign_country( $country ) {
		return true;
	}

	public static function cdi_whereis_parcel( $order_id, $trackingcode ) {
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
			if ( $trackingcode ) {
				$splittracking = str_replace( 'C', '', $trackingcode );
				$splittracking = str_replace( 'I', '', $splittracking );
				$splittracking = explode( 'D', $splittracking );
				if ( $splittracking['0'] && $splittracking['1'] ) { // CDI contrat and order num exist ?
					$track_order_id = $splittracking['1'];
					$trackingstatus = $order->get_meta( '_cdi_meta_collect_status' );
					if ( $trackingstatus ) {
						$lib_status = str_replace( array( 'preparation', 'atcollectpoint', 'courier', 'delivered', 'customeragreement' ), array( __( 'In preparation for', 'cdi' ), __( 'At collect point', 'cdi' ), __( 'Courier is running', 'cdi' ), __( 'Delivered to customer', 'cdi' ), __( 'Customer agreement', 'cdi' ) ), $trackingstatus );
						$msgsuivicolis = '=> ' . $lib_status;
					} else {
						$msgsuivicolis = '=> No tracking.';
					}
					return $msgsuivicolis;
				}
			}
		}
		  $msgsuivicolis = '=> No tracking.';
		  return $msgsuivicolis;
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
		$format = 'PDF_10x15_300dpi';
		return $format;
	}

}

?>
