<?PHP

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
/******************************************************************************************************/
/* Functions and Controls : the shipment tracking code is present when status become completed        */
/******************************************************************************************************/
class cdi_c_Function {
	// ***************************************************************************************************
	public static function init() {
		add_action( 'admin_notices', __CLASS__ . '::cdi_admin_notice' );
		add_action( 'before_delete_post', __CLASS__ . '::cdi_delete_order' );
	}
	// ***************************************************************************************************
	public static function cdi_admin_notice() {
		$cdi_o_notice_display = get_option( 'cdi_o_notice_display' );
		if ( $cdi_o_notice_display !== 'nothing' ) {
			echo '<div class="updated notice"><p>';
			echo wp_kses_post( $cdi_o_notice_display );
			echo '</p></div>)';
			update_option( 'cdi_o_notice_display', 'nothing' );
		} else {
			add_option( 'cdi_o_notice_display', 'nothing' );
		}
	}
	// ***************************************************************************************************
	public static function cdi_delete_order( $idorder ) {
		// Automatic clean of cdistore when an order is suppress
		$order = wc_get_order( $idorder );
		if ( !$order ) {
			return false;
		}
		$upload_dir = wp_upload_dir();
		$dircdistore = trailingslashit( $upload_dir['basedir'] ) . 'cdistore';
		$url = wp_nonce_url( 'plugins.php?page=collect-deliver-interface' );
		if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, null ) ) ) {
			self::cdi_debug( __LINE__, __FILE__, 'error - request creds', 'tec' );
			return false;
		}
		if ( ! WP_Filesystem( $creds ) ) {
			self::cdi_debug( __LINE__, __FILE__, 'error - creds not valid', 'tec' );
			return false;
		}
		global $wp_filesystem;
		if ( ! file_exists( $dircdistore ) ) {
			return false;
		}
		$filename = trailingslashit( $dircdistore ) . 'CDI-' . 'label' . '-' . $idorder . '.txt';
		$result = $wp_filesystem->delete( $filename );
		$filename = trailingslashit( $dircdistore ) . 'CDI-' . 'cn23' . '-' . $idorder . '.txt';
		$result = $wp_filesystem->delete( $filename );
		return true;
	}
	// ***************************************************************************************************
	public static function cdi_uploads_encrypt_contents( $filecontent ) {
		$ec_filecontent = $filecontent;
		$key = get_option( 'keycdistore' );
		if ( ! $key or $key == '' ) {
			$key = bin2hex( openssl_random_pseudo_bytes( 16, $cstrong ) );
			update_option( 'keycdistore', $key );
		}
		if ( get_option( 'cdi_o_settings_encryptioncdistore' ) == 'yes' ) {
			$iv = openssl_random_pseudo_bytes( 16 ); // IV of 16 bytes long with aes-256-ctr
			// $iv = "1234567812345678" ; //For testing
			$cipher = 'aes-256-ctr';
			if ( in_array( $cipher, openssl_get_cipher_methods() ) ) {
				$ec_filecontent = openssl_encrypt( $filecontent, $cipher, $key, $options = 0, $iv );
				$ec_filecontent = $iv . $ec_filecontent;
			}
		}
		return $ec_filecontent;
	}
	// ***************************************************************************************************
	public static function cdi_uploads_decrypt_contents( $filecontent, $idorder, $type ) {
		$de_filecontent = $filecontent;
		$begincontent = substr( $filecontent, 0, 5 );
		$beginidorder = substr( $idorder, 0, 3 );
		$todecrypt = false;
		// case no decrypt for pdf and for CSV
		if ( $beginidorder == 'HI-' ) { // CSV
			if ( get_option( 'cdi_o_settings_encryptioncdistore' ) == 'yes' ) {
				$todecrypt = true;
			}
		} else { // PDF
			if ( $begincontent !== 'JVBER' ) {
				$todecrypt = true;
			}
		}
		if ( $todecrypt ) {
			$key = get_option( 'keycdistore' );
			$iv = substr( $filecontent, 0, 16 ); // IV of 16 bytes long with aes-256-ctr
			// $iv = "1234567812345678" ; //For testing
			$filecontent = substr( $filecontent, 16 );
			$cipher = 'aes-256-ctr';
			$de_filecontent = openssl_decrypt( $filecontent, $cipher, $key, $options = 0, $iv );
		}
		return $de_filecontent;
	}
	// ***************************************************************************************************
	public static function cdi_uploads_put_contents( $idorder, $type, $filecontent ) {	
		if ( $type !== 'label' and $type !== 'cn23' and $type !== 'bordereau' ) {
			return false;
		}
		$upload_dir = wp_upload_dir();
		$dircdistore = trailingslashit( $upload_dir['basedir'] ) . 'cdistore';
		$url = wp_nonce_url( 'plugins.php?page=collect-deliver-interface' );
		if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, null ) ) ) {
			self::cdi_debug( __LINE__, __FILE__, 'error - request creds', 'tec' );
			return false;
		}
		if ( ! WP_Filesystem( $creds ) ) {
			self::cdi_debug( __LINE__, __FILE__, 'error - creds not valid', 'tec' );
			return false;
		}
		global $wp_filesystem;
		if ( ! file_exists( $dircdistore ) ) { // create cdistore dir if not exist
			if ( ! $wp_filesystem->mkdir( $dircdistore ) ) {
				self::cdi_debug( __LINE__, __FILE__, 'error - create dir', 'tec' );
				return false;
			}
		}
		chmod( $dircdistore, 0750 ); // to avoid external reading
		$filename = trailingslashit( $dircdistore ) . 'CDI-' . $type . '-' . $idorder . '.txt';		
		$filecontent = self::cdi_uploads_encrypt_contents( $filecontent );
		$result = $wp_filesystem->delete( $filename ); // if exist suppress before replace
		if ( ! $wp_filesystem->put_contents( $filename, $filecontent, FS_CHMOD_FILE ) ) {
			self::cdi_debug( __LINE__, __FILE__, 'error - create file', 'tec' );
			return false;
		}
		chmod( $filename, 0640 ); // to avoid external reading
		if (is_numeric($idorder) ) {
			$order = wc_get_order( $idorder );
			if ( $order ) {
				$order->update_meta_data( '_cdi_meta_exist_uploads_' . $type, true );
				$order->save() ;	
			}
		}else{
			self::cdi_debug( __LINE__, __FILE__, 'error - order id not numeric', 'tec' );	
		}
		return true;
	}
	// ***************************************************************************************************
	public static function cdi_uploads_get_contents( $idorder, $type ) {
		if ( $type !== 'label' and $type !== 'cn23' and $type !== 'bordereau' ) {
			return false;
		}
		$upload_dir = wp_upload_dir();
		$dircdistore = trailingslashit( $upload_dir['basedir'] ) . 'cdistore';
		$url = wp_nonce_url( 'plugins.php?page=collect-deliver-interface' );
		if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, null ) ) ) {
			self::cdi_debug( __LINE__, __FILE__, 'error - request creds', 'tec' );
			return false;
		}
		if ( ! WP_Filesystem( $creds ) ) {
			self::cdi_debug( __LINE__, __FILE__, 'error - creds not valid', 'tec' );
			return false;
		}
		global $wp_filesystem;
		if ( ! file_exists( $dircdistore ) ) {
			self::cdi_debug( __LINE__, __FILE__, 'error - dir not exist', 'tec' );
			return false;
		}
		$filename = trailingslashit( $dircdistore ) . 'CDI-' . $type . '-' . $idorder . '.txt';		
		$filecontent = $wp_filesystem->get_contents( $filename );
		if ( ! $filecontent ) {
			self::cdi_debug( __LINE__, __FILE__, 'error - file get', 'tec' );
			return false;
		}
		$filecontent = self::cdi_uploads_decrypt_contents( $filecontent, $idorder, $type );
		return $filecontent;
	}
	// ***************************************************************************************************
	public static function cdi_cn23_country( $country, $zipcode = null ) {
		$nocn23 = get_option( 'cdi_o_settings_Nocn23ContryCodes' );
		if ( ! $nocn23 ) {
			$nocn23 = 'DE,AT,BE,BG,CY,DK,ES,EE,FI,FR,GR,HU,IE,IT,LV,LT,LU,MT,NL,PL,PT,CZ,RO,IE,SK,SI,SE'; // For Back compatibility
		}
		$array_nocn23 = explode( ',', $nocn23 );
		if ( ! in_array( $country, $array_nocn23 ) ) {
			return true;
		} else {
			// zipcode exemptions process
			$array_countrieslist = explode( ';', get_option( 'cdi_o_settings_Cn23ZipcodeExemptions' ) );
			foreach ( $array_countrieslist as $zipcodelist ) {
				$array_zipcode = explode( '=', $zipcodelist );
				if ( $array_zipcode[0] == $country ) {
					if ( ! ( strpos( $array_zipcode[1], $zipcode ) === false ) ) {
						return true;
					}
				}
			}
			return false;
		}
	}
	// ***************************************************************************************************
	public static function cdi_nochoicereturn_country( $id_order, $country ) {
		$order = wc_get_order( $id_order );
		$carrier = $order->get_meta( '_cdi_meta_carrier' );
		$carrier = self::cdi_fallback_carrier( $carrier );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_nochoicereturn_country';
		$return = ( $route )( $country );
		return $return;
	}
	// ***************************************************************************************************
	public static function cdi_outremer_country( $country ) {
		if ( in_array( $country, array( 'MQ', 'GP', 'RE', 'GF', 'YT', 'PM', 'MF', 'BL', 'NC', 'PF', 'TF', 'WF' ) ) ) {
			return true;
		} else {
			return false;
		}
	}
	// ***************************************************************************************************
	public static function cdi_outremer_country_ftd( $country ) {
		if ( in_array( $country, array( 'MQ', 'GP', 'RE', 'GF' ) ) ) {
			return true;
		} else {
			return false;
		}
	}
	// ***************************************************************************************************
	public static function cdi_get_items_chosen( $order ) {
		$items = $order->get_items();
		  $chosen_products = $order->get_meta( '_cdi_chosen_products' );
		  $shiptype_option = get_option( 'cdi_o_settings_shippingpackageincart' );
		if ( empty( $chosen_products )  // compatibility for old orders
		  or empty( $shiptype_option )
		  or ( $shiptype_option == 'cart' ) // Order is not in WC multi shipping packages (Market places)
		  ) {
			$returnitems = $items;
		} else { // Order in WC multi shipping packages (Market places)
			$returnitems = array();
			foreach ( $items as $item ) {
				if ( is_array( $chosen_products ) && in_array( $item['product_id'], $chosen_products ) ) { // To ensure to get only products in shipping package
					$returnitems[] = $item;
				}
			}
			if ( empty( $returnitems ) ) { // Nothing found. It seems a product item swap has been done (probably a translation plugin running)
				$returnitems = apply_filters( 'cdi_filterarray_itemslist_ordered_shippingpackage', $items, $chosen_products, $order ); // The custom filter has to apply the swap of items
			}
		}
		  return $returnitems;
	}
	// ***************************************************************************************************
	public static function cdi_calc_totalnetweight( $order ) {
		$items = self::cdi_get_items_chosen( $order );
		$total_weight = 0;
		foreach ( $items as $item ) {
			$quantity = $item['quantity'];
			$product_id = $item['variation_id'];
			if ( $product_id == 0 ) { // No variation for that one
				$product_id = $item['product_id'];
			}
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$weight = $product->get_weight();
				if ( is_numeric( $weight ) && is_numeric( $quantity ) ) {
					$item_weight = ( $weight * $quantity );
				} else {
					$item_weight = 0;
				}
				$total_weight += $item_weight;
			}
		}
		if ( get_option( 'woocommerce_weight_unit' ) == 'kg' ) { // Convert kg to g
			$total_weight = $total_weight * 1000;
		}

		  return $total_weight;
	}
	// ***************************************************************************************************
	public static function cdi_cn23_calc_shipping( $order ) {
		$items = $order->get_items( 'shipping' );
		$costshipping = 0;
		if ( $order->get_meta( '_cdi_refshippingmethod' ) ) { // case order by customer
			$arrshippingmethod = explode( ':', $order->get_meta( '_cdi_refshippingmethod' ) );
			foreach ( $items as $item ) {
				if ( $item['instance_id'] == $arrshippingmethod['1'] ) {
					$costshipping = $item['total'];
					break;
				}
			}
		} else { // case order by admin or no method
			foreach ( $items as $item ) {
				$costshipping = $item['total'];
				break;
			}
		}
		  return $costshipping; // exVAT shipping cost returned
	}
	// ***************************************************************************************************
	public static function cdi_stat( $ref ) {
		$stat = get_option( 'cdi_o_stat' );
		$month = date( 'Ym' );
		if ( ! isset( $stat[ $month ][ $ref ] ) ) {
			$stat[ $month ][ $ref ] = 0;
		}
		$stat[ $month ][ $ref ] = $stat[ $month ][ $ref ] + 1;
		$monthpurge = date( 'Ym', strtotime( date( 'Ym' ) . ' - 6 months' ) );
		foreach ( $stat as $key => $month ) {
			if ( ( $key < $monthpurge ) ) {
				unset( $stat[ $key ] );
			}
		}
		update_option( 'cdi_o_stat', $stat );
	}
	// ***************************************************************************************************
	public static function cdi_sanitize_voie( $string ) {
		$string = sanitize_text_field( $string );
		$excludespecial = array( "'", '’', '(', '_', ')', '=', ';', ':', '!', '#', '{', '[', '|', '^', '@', ']', '}', 'µ', '?', '§', '*', '"', "'", ',' );
		$string = str_replace( $excludespecial, ' ', $string );
		return $string;
	}
	// ***************************************************************************************************
	public static function cdi_sanitize_voie_om( $string ) {
		// Idem sauf '’' et "'"
		$string = sanitize_text_field( $string );
		$excludespecial = array( '(', '_', ')', '=', ';', ':', '!', '#', '{', '[', '|', '^', '@', ']', '}', 'µ', '?', '§', '*', '"', ',' );
		$string = str_replace( $excludespecial, ' ', $string );
		return $string;
	}
	// ***************************************************************************************************
	public static function cdi_sanitize_name( $string ) {
		$string = sanitize_text_field( $string );
		$excludespecial = array( "'", '’', '(', '_', ')', '=', ';', ':', '!', '#', '{', '[', '|', '^', '@', ']', '}', 'µ', '?', '§', '*', '"', ',' );
		$string = str_replace( $excludespecial, '', $string );
		$excludespecial = array( '.', '%', '/', '&' );
		$string = str_replace( $excludespecial, ' ', $string );
		return $string;
	}
	// ***************************************************************************************************
	public static function cdi_sanitize_string( $string ) {
		$excludespecial = array( "'", '%', '/', '-', '&', '.' );
		$string = str_replace( $excludespecial, ' ', $string );
		$string = strtoupper( $string );
		return $string;
	}
	// ***************************************************************************************************
	public static function cdi_sanitize_accents( $string ) {
		$return = $string;
		$accents = array( 'À', 'Á', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ' );
		$without = array( 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y' );
		$return = str_replace( $accents, $without, $return );
		return $return;
	}
	// ***************************************************************************************************
	public static function cdi_sanitize_array( $input_array ) {
		$new_input_sanitize = array();
		foreach ( $input_array as $key => $val ) {
			if ( ! is_array( $val ) ) {
				$new_input_sanitize[ sanitize_text_field( $key ) ] = sanitize_text_field( $val );
			} else {
				$new_input_sanitize[ $key ] = $val;
			}
		}
		return $new_input_sanitize;
	}
	// ***************************************************************************************************
	public static function cdi_fallback_carrier( $carrier ) {
		$return = $carrier;
		if ( ! $carrier or $carrier = '' ) {
			$return = 'colissimo';
		}
		return $return;
	}
	// ***************************************************************************************************
	public static function cdi_get_libelle_carrier( $carrier ) {
		$return = str_replace( array( 'colissimo', 'mondialrelay', 'ups', 'collect', 'deliver', 'notcdi' ), array( 'Colissimo', 'Mondial Relay', 'UPS', 'Collect', 'Deliver', 'Not CDI' ), $carrier );
		if ( $carrier == 'notcdi' ) {
		  $return = get_option( 'cdi_o_settings_notcdi_labelname' ) ;
		}
		if ( !$return ) {
			$return = 'Custom' ;
		}
		$return = apply_filters( 'cdi_filterstring_libelle_carrier', $return );
		return $return ;
	}
	// ***************************************************************************************************
	public static function ssl_private_decrypt( $source, $key ) {
		$maxlength = 128;
		$output = '';
		while ( $source ) {
			$input = substr( $source, 0, $maxlength );
			$source = substr( $source, $maxlength );
			$ok = openssl_private_decrypt( $input, $out, $key );
			$output .= $out;
		}
		return $output;
	}
	// ***************************************************************************************************
	public static function cdi_array_for_carrier( $row ) {
		global $woocommerce;
		global $wpdb;
		if ( is_numeric( $row ) ) {
			$cdi_order_id  = $row;
		} else {
			$cdi_order_id  = $row->cdi_order_id;
		}
		$order = wc_get_order( $cdi_order_id );
		$ordernumber = $order->get_order_number();
		$order_date = $order->get_date_created();

		$status_wc = $order->get_status();
		$status_cdi = $order->get_meta( '_cdi_meta_status' );

		$carrier = $order->get_meta( '_cdi_meta_carrier' );
		if ( ! $carrier ) {
			$carrier = 'colissimo'; // for retro compatibility
		}
		$shipping_first_name = $order->get_shipping_first_name();
		$shipping_first_name = remove_accents( $shipping_first_name );
			 $shipping_first_name = self::cdi_sanitize_name( $shipping_first_name );
		$shipping_last_name = $order->get_shipping_last_name();
		$shipping_last_name = remove_accents( $shipping_last_name );
			 $shipping_last_name = self::cdi_sanitize_name( $shipping_last_name );
		$shipping_company = $order->get_shipping_company();
		$shipping_company = remove_accents( $shipping_company );
			 $shipping_company = self::cdi_sanitize_name( $shipping_company );
		if ( ! $shipping_company ) {
			$shipping_company = $shipping_last_name; }
		$shipping_address_1 = $order->get_shipping_address_1();
		$shipping_address_1 = remove_accents( $shipping_address_1 );
			 $shipping_address_1 = self::cdi_sanitize_voie( $shipping_address_1 );
		$shipping_address_2 = $order->get_shipping_address_2();
		$shipping_address_2 = remove_accents( $shipping_address_2);
			 $shipping_address_2 = self::cdi_sanitize_voie( $shipping_address_2 );
		$shipping_address_3 = $order->get_meta( '_shipping_address_3' );
		$shipping_address_3 = remove_accents( $shipping_address_3 );
			 $shipping_address_3 = self::cdi_sanitize_voie( $shipping_address_3 );
		$shipping_address_4 = $order->get_meta( '_shipping_address_4' );
		$shipping_address_4 = remove_accents( $shipping_address_4 );
			 $shipping_address_4 = self::cdi_sanitize_voie( $shipping_address_4 );
		$shipping_city = $order->get_shipping_city();
		$shipping_city = remove_accents( $shipping_city );
			 $shipping_city = self::cdi_sanitize_voie( $shipping_city );
		$shipping_postcode = $order->get_shipping_postcode();
		$shipping_postcode = remove_accents( $shipping_postcode );
			 $shipping_postcode = self::cdi_sanitize_voie( $shipping_postcode );
		$shipping_country = $order->get_shipping_country();
		$shipping_country = remove_accents( $shipping_country );
			 $shipping_country = self::cdi_sanitize_voie( $shipping_country );
		$shipping_state = $order->get_shipping_state();
		$shipping_state = remove_accents( $shipping_state );
			 $shipping_state = self::cdi_sanitize_voie( $shipping_state );
		if ( $shipping_state ) {
			$shipping_city_state = $shipping_city . ' ' . $shipping_state;
		} else {
			$shipping_city_state = $shipping_city;
		}
		$billing_phone = $order->get_billing_phone();
		$billing_phone = remove_accents( $billing_phone );
		$billing_email = $order->get_billing_email( );
		$billing_email = remove_accents( $billing_email );
		$customer_message = $order->get_customer_note();
		$customer_message = remove_accents( $customer_message );
		$cdi_meta_departure = $order->get_meta( '_cdi_meta_departure' );
		$cdi_meta_departure = self::cdi_sanitize_voie( $cdi_meta_departure );
			$cdi_departure_cp = substr( $cdi_meta_departure, 0, 5 );
			$cdi_departure_localite = substr( $cdi_meta_departure, 6 );
		$cdi_meta_typeparcel = $order->get_meta( '_cdi_meta_typeparcel' );
		$cdi_meta_parcelweight = $order->get_meta( '_cdi_meta_parcelweight' );
		$cdi_meta_collect = $order->get_meta( '_cdi_meta_collectcar' );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_function_withoutsign_country';
		if ( ! ( $route )( $shipping_country ) ) {
			$order->update_meta_data( '_cdi_meta_signature', 'yes' );
			$order->save() ;
		}
		$cdi_meta_signature = $order->get_meta( '_cdi_meta_signature' );
		// Additionnal insurance display
		$cdi_meta_additionalcompensation = $order->get_meta( '_cdi_meta_additionalcompensation' );
		$cdi_meta_amountcompensation = $order->get_meta( '_cdi_meta_amountcompensation' );
		// End Additionnal insurance display display

		$cdi_meta_returnReceipt = $order->get_meta( '_cdi_meta_returnReceipt' ); // Return avis réception
		// End return avis réception
		if ( self::cdi_nochoicereturn_country( $cdi_order_id, $shipping_country ) ) { // Return internationnal display
			$cdi_meta_typereturn = $order->get_meta( '_cdi_meta_typereturn' );
		} else {
			$cdi_meta_typereturn = '';
		} //  End Return internationnal display
		if ( self::cdi_outremer_country_ftd( $shipping_country ) ) { // OM ftd
			$cdi_meta_ftd = $order->get_meta( '_cdi_meta_ftd' );
		} else {
			$cdi_meta_ftd = '';
		} //  End OM ftd

		if ( 'ordernumber' == get_option( 'cdi_o_settings_parcelreference' ) ) {
			$sender_parcel_ref = $order->get_order_number();
		} else {
			$sender_parcel_ref = $cdi_order_id;
		}
		$sender_parcel_ref = apply_filters( 'cdi_filterstring_sender_parcel_ref', $sender_parcel_ref );
		$carrier_instructions = apply_filters( 'cdi_filterstring_carrier_instructions', '' );

		$shippingmethod = $order->get_meta( '_cdi_refshippingmethod' );
		$method_name = $order->get_meta( '_cdi_meta_shippingmethod_name' );
		$cdi_meta_pickupLocationId = $order->get_meta( '_cdi_meta_pickupLocationId' );
		$pickupLocationlabel = $order->get_meta( '_cdi_meta_pickupLocationlabel' );
		$pickupfulladdress = $order->get_meta( '_cdi_meta_pickupfulladdress' );
		$tracking = $order->get_meta( '_cdi_meta_tracking' );
		$parcelNumberPartner = $order->get_meta( '_cdi_meta_parcelNumberPartner' );
		$urllabel = $order->get_meta( '_cdi_meta_urllabel' );
		$cdi_meta_productCode = $order->get_meta( '_cdi_meta_productCode' );

		$cdi_meta_cn23_shipping = $order->get_meta( '_cdi_meta_cn23_shipping' );
		$cdi_meta_cn23_category = $order->get_meta( '_cdi_meta_cn23_category' );

		$array_for_carrier = array( 'order_id' => $cdi_order_id );
		$array_for_carrier['ordernumber']  = $ordernumber;
		$array_for_carrier['order_date']  = $order_date;

		$array_for_carrier['status_wc']  = $status_wc;
		$array_for_carrier['status_cdi']  = $status_cdi;

		$array_for_carrier['shipping_first_name']  = $shipping_first_name;
		$array_for_carrier['shipping_last_name']  = $shipping_last_name;
		$array_for_carrier['shipping_company']  = $shipping_company;
		$array_for_carrier['shipping_address_1']  = $shipping_address_1;
		$array_for_carrier['shipping_address_2']  = $shipping_address_2;
		$array_for_carrier['shipping_address_3']  = $shipping_address_3;
		$array_for_carrier['shipping_address_4']  = $shipping_address_4;
		$array_for_carrier['shipping_city']  = $shipping_city;
		$array_for_carrier['shipping_postcode']  = $shipping_postcode;
		$array_for_carrier['shipping_country']  = $shipping_country;
		$array_for_carrier['shipping_state']  = $shipping_state;
		$array_for_carrier['shipping_city_state']  = $shipping_city_state;
		$array_for_carrier['billing_phone']  = $billing_phone;
		$array_for_carrier['billing_email']  = $billing_email;
		$array_for_carrier['carrier']  = $carrier;
		$array_for_carrier['customer_message']  = $customer_message;
		$array_for_carrier['departure']  = $cdi_meta_departure;
		$array_for_carrier['departure_cp']  = $cdi_departure_cp;
		$array_for_carrier['departure_localite']  = $cdi_departure_localite;
		$array_for_carrier['parcel_type']  = $cdi_meta_typeparcel;
		$array_for_carrier['parcel_weight']  = $cdi_meta_parcelweight;
		$array_for_carrier['parcel_collect']  = $cdi_meta_collect;
		$array_for_carrier['signature']  = $cdi_meta_signature;
		$array_for_carrier['additional_compensation']  = $cdi_meta_additionalcompensation;
		$array_for_carrier['compensation_amount']  = $cdi_meta_amountcompensation;

		$array_for_carrier['returnReceipt']  = $cdi_meta_returnReceipt;
		$array_for_carrier['return_type']  = $cdi_meta_typereturn;
		$array_for_carrier['ftd']  = $cdi_meta_ftd;

		$array_for_carrier['sender_parcel_ref']  = $sender_parcel_ref;
		$array_for_carrier['carrier_instructions']  = $carrier_instructions;

		$array_for_carrier['shippingmethod']  = $shippingmethod;
		$array_for_carrier['method_name']  = $method_name;
		$array_for_carrier['pickup_Location_id']  = $cdi_meta_pickupLocationId;
		$array_for_carrier['pickupLocationlabel']  = $pickupLocationlabel;
		$array_for_carrier['pickupfulladdress']  = $pickupfulladdress;
		$array_for_carrier['tracking']  = $tracking;
		$array_for_carrier['parcelNumberPartner']  = $parcelNumberPartner;
		$array_for_carrier['urllabel']  = $urllabel;

		$array_for_carrier['product_code']  = $cdi_meta_productCode;

		$array_for_carrier['cn23_shipping']  = $cdi_meta_cn23_shipping;
		$array_for_carrier['cn23_category']  = $cdi_meta_cn23_category;

		$items = self::cdi_get_items_chosen( $order );
		$nbart = 0;
		foreach ( $items as $item ) {
			$cdi_meta_cn23_article_description = $order->get_meta( '_cdi_meta_cn23_article_description_' . $nbart );
			$cdi_meta_cn23_article_weight = $order->get_meta( '_cdi_meta_cn23_article_weight_' . $nbart );
			$cdi_meta_cn23_article_quantity = $order->get_meta( '_cdi_meta_cn23_article_quantity_' . $nbart );
			$cdi_meta_cn23_article_value = $order->get_meta( '_cdi_meta_cn23_article_value_' . $nbart );
			if ( $order->get_meta( '_cdi_meta_cn23_category' ) == '3' ) { // CN23 HS code display
				$cdi_meta_cn23_article_hstariffnumber = $order->get_meta( '_cdi_meta_cn23_article_hstariffnumber_' . $nbart );
			} else {
				$cdi_meta_cn23_article_hstariffnumber = '';
			} //  End CN23 HS code display
			$cdi_meta_cn23_article_origincountry = $order->get_meta( '_cdi_meta_cn23_article_origincountry_' . $nbart );

			$array_for_carrier[ 'cn23_article_description_' . $nbart ]  = $cdi_meta_cn23_article_description;
			$array_for_carrier[ 'cn23_article_weight_' . $nbart ]  = $cdi_meta_cn23_article_weight;
			$array_for_carrier[ 'cn23_article_quantity_' . $nbart ]  = $cdi_meta_cn23_article_quantity;
			$array_for_carrier[ 'cn23_article_value_' . $nbart ]  = $cdi_meta_cn23_article_value;
			$array_for_carrier[ 'cn23_article_hstariffnumber_' . $nbart ]  = $cdi_meta_cn23_article_hstariffnumber;
			$array_for_carrier[ 'cn23_article_origincountry_' . $nbart ]  = $cdi_meta_cn23_article_origincountry;
			$nbart = $nbart + 1;
		}
		return $array_for_carrier;
	}
	// ***************************************************************************************************
	public static function cdi_debug( $line, $file, $var, $type = 'msg' ) {
		global $wpdb;
		$x = plugin_dir_path( __FILE__ ); // magic trick to shorten the path
		$x = str_replace( '/includes/', '', $x );
		$file = str_replace( $x, '', $file );
		if ( get_option( 'cdi_o_settings_moduletolog' ) == 'debug' ) {
			$msg = '*** LOG CDI(' . $type . ') - LINE:' . $line . ' FILE:' . $file . ' ***: ' . print_R( $var, true );
			file_put_contents( WP_CONTENT_DIR . '/cdilog.log', '[' . date( 'Y-m-d H:i:s' ) . '] ' . $msg . PHP_EOL, FILE_APPEND | LOCK_EX );
		}
	}
	// ***************************************************************************************************
	public static function cdi_get_woo_version_number() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_folder = get_plugins( '/' . 'woocommerce' );
		$plugin_file = 'woocommerce.php';
		if ( isset( $plugin_folder[ $plugin_file ]['Version'] ) ) {
			return $plugin_folder[ $plugin_file ]['Version'];
		} else {
			return null;
		}
	}
	// ***************************************************************************************************
	public static function cdi_sanitize_phone( $string ) {
		// Keep only number and the heading +
		$string = preg_replace( '/^\+/', '00000000', $string ); // Suppress international + and replace by a 00000000 patttern
		$string = preg_replace( '/[^0-9]+/', '', $string ); // Clean with only numbers
		$string = preg_replace( '/^00000000/', '+', $string ); // Reset international + if exist in input
		return $string;
	}
	// ***************************************************************************************************
	public static function cdi_sanitize_mobilenumber( $MobileNumber, $country ) {
		$MobileNumber = self::cdi_sanitize_phone( $MobileNumber );
		switch ( $country ) {
			case 'FR':
				// Si le numéro commence par +33X, 0033X, +330X ou 00330X il est nécessaire d'avoir converti le début en 0X (où X = 6 ou 7)
				$MobileNumber = preg_replace( '/^003306/', '06', $MobileNumber );
				$MobileNumber = preg_replace( '/^003307/', '07', $MobileNumber );
				$MobileNumber = preg_replace( '/^\+3306/', '06', $MobileNumber );
				$MobileNumber = preg_replace( '/^\+3307/', '07', $MobileNumber );
				$MobileNumber = preg_replace( '/^00336/', '06', $MobileNumber );
				$MobileNumber = preg_replace( '/^00337/', '07', $MobileNumber );
				$MobileNumber = preg_replace( '/^\+336/', '06', $MobileNumber );
				$MobileNumber = preg_replace( '/^\+337/', '07', $MobileNumber );
				switch ( $MobileNumber ) {
					case ( preg_match( '/^06/', $MobileNumber ) ? true : false ):
						break;
					case ( preg_match( '/^07/', $MobileNumber ) ? true : false ):
						break;
					default:
						$MobileNumber = ''; // Erase mobile number if invalid
				}
				break;
		}
		return $MobileNumber;
	}
	// ***************************************************************************************************
	/**
	 *
	 * The most advanced method of serialization.
	 *
	 * @param mixed              $obj => can be an objectm, an array or string. may contain unlimited number of subobjects and subarrays
	 * @param string             $wrapper => main wrapper for the xml
	 * @param array (key=>value) $replacements => an array with variable and object name replacements
	 * @param boolean            $add_header => whether to add header to the xml string
	 * @param array (key=>value) $header_params => array with additional xml tag params
	 * @param string             $node_name => tag name in case of numeric array key
	 * @param boolean            $delete_empty => whether not suppress empty entries in xml output	 
	 */
	public static function generateValidXmlFromMixiedObj( $obj, $wrapper = null, $replacements = array(), $add_header = true, $header_params = array(), $node_name = 'node', $delete_empty = true ) {
		$xml = '';
		if ( $add_header ) {
			$xml .= self::generateHeader( $header_params );
		}
		if ( $wrapper != null ) {
			$xml .= '<' . $wrapper . '>';
		}
		if ( is_object( $obj ) ) {
			$node_block = strtolower( get_class( $obj ) );
			if ( isset( $replacements[ $node_block ] ) ) {
				$node_block = $replacements[ $node_block ];
			}
			$xml .= '<' . $node_block . '>';
			$vars = get_object_vars( $obj );
			if ( ! empty( $vars ) ) {
				foreach ( $vars as $var_id => $var ) {
					if ( isset( $replacements[ $var_id ] ) ) {
						$var_id = $replacements[ $var_id ];
					}
			 		if ($var or $delete_empty === false) {					
						$xml .= '<' . $var_id . '>';
						$xml .= self::generateValidXmlFromMixiedObj( $var, null, $replacements, false, null, $node_name );
						$xml .= '</' . $var_id . '>';
					}
				}
			}
			$xml .= '</' . $node_block . '>';
		} else if ( is_array( $obj ) ) {
			foreach ( $obj as $var_id => $var ) {
			   if ($var or $delete_empty === false) {
				if ( ! is_object( $var ) ) {
					if ( is_numeric( $var_id ) ) {
						$var_id = $node_name;
					}
					if ( isset( $replacements[ $var_id ] ) ) {
						$var_id = $replacements[ $var_id ];
					}									
					$xml .= '<' . $var_id . '>';					
				}					
				$xml .= self::generateValidXmlFromMixiedObj( $var, null, $replacements, false, null, $node_name );				
				if ( ! is_object( $var ) ) {					
					$xml .= '</' . $var_id . '>';					
				}
			    }
			}
		} else {
		     if ($obj or $delete_empty === false) {
			$xml .= htmlspecialchars( $obj, ENT_QUOTES );
		     }
		}
		if ( $wrapper != null ) {
			$xml .= '</' . $wrapper . '>';
		}
		return $xml;
	}
	/**
	 *
	 * xml header generator
	 *
	 * @param array $params
	 */
	public static function generateHeader( $params = array() ) {
		$basic_params = array(
			'version' => '1.0',
			'encoding' => 'UTF-8',
		);
		if ( ! empty( $params ) ) {
			$basic_params = array_merge( $basic_params, $params );
		}
		$header = '<?xml';
		foreach ( $basic_params as $k => $v ) {
			$header .= ' ' . $k . '=' . $v;
		}
		$header .= ' ?>';
		return $header;
	}
	// ***************************************************************************************************
	public static function prettyxml( $xmlstring ) {
		$dom = new \DOMDocument('1.0'); 
		$dom->preserveWhiteSpace = true; 
		$dom->formatOutput = true; 
		$dom->loadXML($xmlstring); 
		$xml_pretty = $dom->saveXML();
		return $xml_pretty ;
	}
	// ***************************************************************************************************
	public static function strToHex( $string ) {
		$hex = '';
		for ( $i = 0; $i < strlen( $string ); $i++ ) {
			$ord = ord( $string[ $i ] );
			$hexCode = dechex( $ord );
			$hex .= substr( '0' . $hexCode, -2 );
		}
		return strToUpper( $hex );
	}
	public static function hexToStr( $hex ) {
		$string = '';
		for ( $i = 0; $i < strlen( $hex ) - 1; $i += 2 ) {
			$string .= chr( hexdec( $hex[ $i ] . $hex[ $i + 1 ] ) );
		}
		return $string;
	}
	// ***************************************************************************************************
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
	public static function sup_line( $string ) {
		$string = str_replace( array( "\r\n", "\r", "\n" ), '', $string );
		return $string;
	}
	// ***************************************************************************************************
	public static function get_openssl_version_number( $patch_as_number = false, $openssl_version_number = null ) {
		// OPENSSL_VERSION_NUMBER parser, works from OpenSSL v.0.9.5b+ (e.g. for use with version_compare())
		// OPENSSL_VERSION_NUMBER is a numeric release version identifier for OpenSSL
		// Syntax: MNNFFPPS: major minor fix patch status (HEX)
		// The status nibble meaning: 0 => development, 1 to e => betas, f => release
		// Examples:
		// - 0x000906023 => 0.9.6b beta 3
		// - 0x00090605f => 0.9.6e release
		// - 0x1000103f  => 1.0.1c
		/**
		* @param Return Patch-Part as decimal number for use with version_compare
		* @param OpenSSL version identifier as hex value $openssl_version_number
		*/
		if ( is_null( $openssl_version_number ) ) {
			$openssl_version_number = OPENSSL_VERSION_NUMBER;
		}
		$openssl_numeric_identifier = str_pad( (string) dechex( $openssl_version_number ), 8, '0', STR_PAD_LEFT );
		$openssl_version_parsed = array();
		$preg = '/(?<major>[[:xdigit:]])(?<minor>[[:xdigit:]][[:xdigit:]])(?<fix>[[:xdigit:]][[:xdigit:]])';
		$preg .= '(?<patch>[[:xdigit:]][[:xdigit:]])(?<type>[[:xdigit:]])/';
		preg_match_all( $preg, $openssl_numeric_identifier, $openssl_version_parsed );
		$openssl_version = false;
		if ( ! empty( $openssl_version_parsed ) ) {
			$alphabet = array(
				1 => 'a',
				2 => 'b',
				3 => 'c',
				4 => 'd',
				5 => 'e',
				6 => 'f',
				7 => 'g',
				8 => 'h',
				9 => 'i',
				10 => 'j',
				11 => 'k',
				12 => 'l',
				13 => 'm',
				14 => 'n',
				15 => 'o',
				16 => 'p',
				17 => 'q',
				18 => 'r',
				19 => 's',
				20 => 't',
				21 => 'u',
				22 => 'v',
				23 => 'w',
				24 => 'x',
				25 => 'y',
				26 => 'z',
			);
			$openssl_version = intval( $openssl_version_parsed['major'][0] ) . '.';
			$openssl_version .= intval( $openssl_version_parsed['minor'][0] ) . '.';
			$openssl_version .= intval( $openssl_version_parsed['fix'][0] );
			$patchlevel_dec = hexdec( $openssl_version_parsed['patch'][0] );
			if ( ! $patch_as_number && array_key_exists( $patchlevel_dec, $alphabet ) ) {
				$openssl_version .= $alphabet[ $patchlevel_dec ]; // ideal for text comparison
			} else {
				$openssl_version .= '.' . $patchlevel_dec; // ideal for version_compare
			}
		}
		return $openssl_version;
	}
	// ***************************************************************************************************
	public static function cdi_get_whereis_parcel( $order_id, $trackingcode ) {
		$order = wc_get_order( $order_id );
		$carrier = $order->get_meta( '_cdi_meta_carrier' );
		$carrier = self::cdi_fallback_carrier( $carrier );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_whereis_parcel';
		$return = ( $route )( $order_id, $trackingcode );
		return $return;
	}
	// ***************************************************************************************************
	public static function cdi_button_private_message() {
		global $woocommerce;
		$authorizefiletypes = ' PNG JPEG JPG GIF TXT LOG CSV' ;
		?><em></em><input name="cdi_private_message" type="submit" value="Message privé à CDI" style="float: left; color:red;" title="Message privé à CDI pour préserver vos données confidentielles non divulgables sur le forum." /><em></em><?php
		
if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_private_message'] ) ) {
	$pseudo  = '';
	$urltopic = '';
	$emailadd = '';
	$votremessage = '';
		
	wp_nonce_field( 'cdi_send_message', 'cdi_send_message_nonce' );

	echo '</p><br class="clear">'
	 . '<div style="border: 5px solid blue; margin-left:18%;"><div style="background-color:white; color:black; padding:15px;">'
	 . '<p><mark>Complétez le formulaire ci-après et validez en bas de page.</mark></p>'
	 . '<div style="border: 5px solid blue; background-color:white; color:black; padding:15px;">'
	 . "<p>Les communications privées à destination du développement CDI doivent toujours être en rapport avec un 'topic' ouvert dans le forum Wordpress du plugin CDI.</p>"
	 . "<p>Elles ne sont pas destinées à apporter une formation sur l'utilisation de Wordpress, de WooCommerce, ou de CDI. Dans ce cas vous devez vous référer aux formations et aides existantes sur ces différents programmes et notamment sur leur forum.</p>"
	 . "<p>De même, elles ne sont pas destinées à se substituer au support des différents transporteurs sur l'utilisation et/ou la documentation de leur API Web Services. Tous ont un service support destiné à l'aide de leurs clients respectifs.</p>"
	 . "<p>L'objet est uniquement de communiquer au développement CDI des informations complémentaires confidentielles ne pouvant pas, et ne devant pas, être publiées sur le forum Wordpress. Et ceci dans le seul but d'une expertise ou d'un diagnostic sur le problème posé dans le 'topic'.</p>"
	 . "<p>Dans le but d'en faire bénéficier tous les participants au forum, la réponse du développement CDI se fera uniquement sur le forum, en ayant soin de caviarder/effacer toute information à caractère sensible ou confidentiel. </p>"
	 . '</div>';

	echo '<p><strong>Les données ci-après sont à renseigner : </strong></p>'
	 . "<p>Votre pseudo sur le forum : <input name='cdi_pseudo' placeholder='Votre pseudo sur le forum ...' type='text' required pattern='.{3,}' value='" . esc_attr( $pseudo ) . "'/></p>"
	 . "<p>Url du Topic sur le forum : <input style='width:85%;' name='cdi_url' placeholder='Url Topic sur le forum ...' type='url' required pattern='.{3,}' value='" . esc_url( $urltopic ) . "'/></p>"
	 . "<p>Email additionnel à informer (optionnel) : <input style='width:30%;' name='cdi_emailadd' placeholder='Adresse mail additionnelle (optionnel) ...' type='email' pattern='.{3,}' value='" . esc_attr( $emailadd ) . "'/></p>"
	 . "<p>Fichiers attachés (optionnel) : <input style='width:83%;' id='cdi_filesadd' name='cdi_filesadd[]' placeholder='Fichiers attachés (optionnel) ...' type='file' multiple /></p>"
	  . "<p style='font-size:smaller;'> (les seuls types autorisés sont : " . esc_attr( $authorizefiletypes ) . " )</p>"
	 . '<p>Votre message : </p>'
	 . "<textarea  name='cdi_message' style='width:100%; height:20em;' required pattern='.{15,}'></textarea>"
	 . '<p></p>'
	 . '<input name="cdi_send_message" type="submit" value="Envoyer" style="float:right; margin-bottom:15px;" title="Envoyer votre message" onClick="window.onbeforeunload = null; document.getElementsByClassName(\'cdimsgpriveenvoie\').style.display = \'block\'; event.preventDefault(); location.reload();" />';

	echo "<p style='width:100%;'>NB: Selon les installations, après le clic 'Envoyer', WooCommerce pourra vous demander par un  popup confirmation de quitter la page courante.</p>"
	 . '<div class="cdimsgpriveenvoie" style="border: 5px solid blue; background-color:white; color:black; padding:15px; display:none;">'
	 . "<p style='font-size: 2em;  text-align: center;'>VOTRE MESSAGE A ÉTÉ ENVOYÉ !</p>"
	 . '</div>'
	 . '<p style="color:white;">-</p>'
	 . '</div></div>';
}

	if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_send_message'] ) && isset( $_POST['cdi_send_message_nonce'] ) && wp_verify_nonce( $_POST['cdi_send_message_nonce'], 'cdi_send_message' ) ) {
		$cdi_pseudo = sanitize_text_field( $_POST['cdi_pseudo'] );
		$cdi_blogname = get_bloginfo( 'name' );
		$cdi_url = esc_url_raw( $_POST['cdi_url'] );
		$cdi_emailadd = sanitize_email( $_POST['cdi_emailadd'] );
		$cdi_message = nl2br( sanitize_text_field( $_POST['cdi_message'] ) );
		$cdi_file_attachements = array() ;
		// Warning: Invalid files, such as "unsafe.TXT.php" or "unsafe.txt.php", when included in the $_FILES variable will cause Wordpress to loop indefinitely with the notice "ob_end_flush(): Failed to send buffer of zlib output compression (0)" (in Wordpress 6.6.2).
		$total = count($_FILES['cdi_filesadd']['name']);
		for( $i=0 ; $i < $total ; $i++ ) {
			$filename = $_FILES['cdi_filesadd']['name'][$i] ;
			$filetype = strtoupper(wp_check_filetype($filename)['ext']) ;
			if ( !$filetype || (strpos($authorizefiletypes, $filetype) == 0) ) {
				WC_Admin_Settings::add_message( __( 'Mail to CDI not sent : not authorized file type ! ', 'cdi' ) .  $filetype);
				return ;
			}
			$tmpFilePath = $_FILES['cdi_filesadd']['tmp_name'][$i];
			if ($tmpFilePath != ""){
  				if ($_FILES['cdi_filesadd']['size'][$i] > 1000000) {
					WC_Admin_Settings::add_message( __( 'Mail to CDI not sent : Files too big !', 'cdi' ) );
					return ;
				}			
				$newFilePath = WP_CONTENT_DIR . "/uploads/" . $_FILES['cdi_filesadd']['name'][$i];
				if(move_uploaded_file($tmpFilePath, $newFilePath)) {
					$cdi_file_attachements[] = WP_CONTENT_DIR . "/uploads/" . $_FILES['cdi_filesadd']['name'][$i];
				}
			}
		}
		$subject     = 'CDI REQUEST - ' . date( 'ymdHis' ) . ' - ' . get_option( 'cdi_installation_id' ) . ' - ' . get_option( 'cdi_o_version' ) . ' - ' . stripslashes( htmlspecialchars_decode( $cdi_pseudo ) ) . ' - ' . stripslashes( htmlspecialchars_decode( $cdi_blogname ) ) . ' - ' . site_url();
		$headers     = 'From: ' . stripslashes( htmlspecialchars_decode( $cdi_blogname ) ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
		$to = 'cdiwoo@gmail.com';
		$headers     = str_replace( 'http://', '', $headers );
		$headers     = str_replace( 'https://', '', $headers );
		$headers     = str_replace( '&amp;', '&', $headers );
		$headers     = $headers . 'Content-Type: text/html' . "\r\n";
		$mailreturn = wp_mail( $to, $subject, stripslashes( htmlspecialchars_decode( $cdi_pseudo ) ) . '<br><br>' . $cdi_url . '<br><br>' . $cdi_emailadd . '<br><br>' . stripslashes( htmlspecialchars_decode( $cdi_message ) ), $headers, $cdi_file_attachements );
		WC_Admin_Settings::add_message( __( 'Your private message has been sent.', 'cdi' ) );
	}
	}
	// ***************************************************************************************************
	public static function cdi_hex_dump( $data, $newline = "\n" ) {
		static $return = '';
		static $from = '';
		static $to = '';
		static $width = 16; // number of bytes per line
		static $pad = '.'; // padding for non-visible characters
		if ( $from === '' ) {
			for ( $i = 0; $i <= 0xFF; $i++ ) {
				$from .= chr( $i );
				$to .= ( $i >= 0x20 && $i <= 0x7E ) ? chr( $i ) : $pad;
			}
		}
		$hex = str_split( bin2hex( $data ), $width * 2 );
		$chars = str_split( strtr( $data, $from, $to ), $width );
		$offset = 0;
		foreach ( $hex as $i => $line ) {
			$return .= sprintf( '%6X', $offset ) . ' : ' . implode( ' ', str_split( $line, 2 ) ) . ' [' . $chars[ $i ] . ']' . $newline;
			$offset += $width;
		}
		return $return;
	}
	// ***************************************************************************************************
	public static function cdi_url_get_remote( $url, $data = null ) {
		// Defaut time-out is 5s
		$args = array(
			'method'      => 'GET',
			'timeout'     => 8,
			'blocking'    => true,
			'headers'     => array(
				'Content-Type' => 'Content-type: application/x-www-form-urlencoded',
			),
		);

		$return = wp_remote_get( $url, $args ); // No data or parameters list included in url
		if ( is_wp_error( $return ) ) {
			$error_message = $return->get_error_message();
			self::cdi_debug( __LINE__, __FILE__, 'Error when calling : ' . $url . ' - Return : ' . $error_message, 'tec' );
			return false;
		}
		return $return;
	}
	// ***************************************************************************************************
	public static function cdi_url_post_remote( $url, $data = null, $headers = null ) {
		// Defaut time-out is 5s
		$args = array(
			'method'      => 'POST',
			'timeout'     => 8,
			'blocking'    => true,
			'headers'     => array(
				'Content-Type' => 'Content-type: application/x-www-form-urlencoded',
			),
		);
		if ( $data == null ) {
			$return = wp_remote_post( $url, $args ); // No data or parameters list included in url
		} else {
			if ( ! is_string( $data ) ) {
				if ($headers !== null) {
					$return = wp_remote_post( $url . '?' . http_build_query( $data ), $args ); // Data is an array structure (as soap, array of parameters, ...)
				}else{
					$return = wp_remote_post( $url . '?' . http_build_query( $data ), $headers ); // Data is an array structure (as soap, array of parameters, ...)
				}
			} else {
				$body = $data;
				$args['body'] = $body;
				$return = wp_remote_post( $url, $args ); // Data is a string structure as xml
			}
		}
		if ( is_wp_error( $return ) ) {
			$error_message = $return->get_error_message();
			self::cdi_debug( __LINE__, __FILE__, 'Error when calling : ' . $url . ' - Return : ' . $error_message, 'tec' );
			return false;
		} else {
			$return = wp_remote_retrieve_body( $return );
		}
		return $return;
	}
	// ***************************************************************************************************
	public static function cdi_checkdatetime12( $datetime ) {
		if ( ! $datetime
		or ! ctype_digit( $datetime )
		or strlen( $datetime ) !== 12
		or ( substr( $datetime, 0, 2 ) < '17' or substr( $datetime, 0, 2 ) > '46' )
		or ( substr( $datetime, 2, 2 ) < '1' or substr( $datetime, 2, 2 ) > '12' )
		or ( substr( $datetime, 4, 2 ) < '1' or substr( $datetime, 4, 2 ) > '31' )
		or ( substr( $datetime, 6, 2 ) < '0' or substr( $datetime, 6, 2 ) > '23' )
		or ( substr( $datetime, 8, 2 ) < '0' or substr( $datetime, 8, 2 ) > '59' )
		or ( substr( $datetime, 10, 2 ) < '0' or substr( $datetime, 10, 2 ) > '59' ) ) {
			return false;
		} else {
			return true;
		}
	}
	// ***************************************************************************************************
	public static function CDI_str_to_noaccent( $str ) {
		$str = preg_replace( '#Ç#', 'C', $str );
		$str = preg_replace( '#È|É|Ê|Ë#', 'E', $str );
		$str = preg_replace( '#@|À|Á|Â|Ã|Ä|Å#', 'A', $str );
		$str = preg_replace( '#Ì|Í|Î|Ï#', 'I', $str );
		$str = preg_replace( '#Ò|Ó|Ô|Õ|Ö#', 'O', $str );
		$str = preg_replace( '#Ù|Ú|Û|Ü#', 'U', $str );
		$str = preg_replace( '#Ý#', 'Y', $str );
		return ( $str );
	}
	// ***************************************************************************************************
	public static function cdi_geolocate_customer() {
		global $woocommerce;
		$addresscustomer = self::cdi_sanitize_voie( self::cdi_sanitize_string( self::cdi_sanitize_accents( $woocommerce->customer->get_shipping_address() ) ) ) . ', '
			  . $woocommerce->customer->get_shipping_postcode() . ' '
			  . self::cdi_sanitize_voie( self::cdi_sanitize_string( self::cdi_sanitize_accents( $woocommerce->customer->get_shipping_city() ) ) ) . ', '
			  . WC()->countries->countries[ $woocommerce->customer->get_shipping_country() ];
		$address = preg_replace( '/\s+/', ' ', $addresscustomer );
		$postdata = array(
			'q' => $address,
			'format' => 'json',
		);
		$result = self::cdi_url_get_remote( 'https://nominatim.openstreetmap.org/search?' . http_build_query( $postdata ) ) ;
		$result = $result['body'] ;		
		if ( $result == '[]' ) { // Empty so address error. Try again without street and in structured request mode
			$zipcode = $woocommerce->customer->get_shipping_postcode();
			$city = self::cdi_sanitize_voie( self::cdi_sanitize_string( self::cdi_sanitize_accents( $woocommerce->customer->get_shipping_city() ) ) );
			$city = preg_replace( '/\s+/', ' ', $city );
			$country = $woocommerce->customer->get_shipping_country();
			$countryname = WC()->countries->countries[ $country ];
			if ( $woocommerce->customer->get_shipping_country() == 'PT' ) {
				$zipcode = substr( $zipcode, 0, 4 ); // Bidouillage honteux pour Portugal
			}
			$postdata = array(
				'city' => $city,
				'postalcode' => $zipcode,
				'state' => $countryname,
				'format' => 'json',
			);		
			$result = self::cdi_url_get_remote( 'https://nominatim.openstreetmap.org/search?' . http_build_query( $postdata ) ) ;
			$result = $result['body'] ;			
			if ( $result == '[]' ) { // Empty so address error. To verify ?
				$err = __( ' ===> Search relays error  - Customer address not found : ', 'cdi' ) . $address;
				self::cdi_debug( __LINE__, __FILE__, $err, 'tec' );
				return false;
			}
		}
		$lat = substr( cdi_c_Reference_Livraisons::get_string_between( $result, '"lat":"', '"' ), 0, 9 );
		$lon = substr( cdi_c_Reference_Livraisons::get_string_between( $result, '"lon":"', '"' ), 0, 9 );
		if ( ! $lat or ! $lon ) { // Process fallback or error
			$lat = null;
			$lon = null;
		}
		$return = array(
			'lat' => $lat,
			'lon' => $lon,
			'addresscustomer' => $addresscustomer,
		);
		return $return;
	}
	// ***************************************************************************************************
	public static function cdi_check_evaluator_syntax( $listmacroclasses ) {
		// refine syntax and replace classic php boolean operators by "and" "or" "not"
		$listmacroclasses = str_replace(
			array( '\r', '\n', '(', ')', '&&', '&amp;&amp;', '||', '!', ';' ),
			array( ' ', ' ', ' ( ', ' ) ', ' and ', ' and ', ' or ', ' not ', ' ; ' ),
			$listmacroclasses
		);
		$listmacroclasses = trim( preg_replace( '/\s+/', ' ', $listmacroclasses ) );
		if ( ! $listmacroclasses or $listmacroclasses == ' ' or $listmacroclasses == '' ) {
			return false;
		}
		$return = $listmacroclasses; // Return if OK
		$arraysplitmacroclasses = explode( ';', $listmacroclasses, 100 );
		// Check structure of the rule and correct characters used
		$structpattern = '/^#[0-9a-zA-Z\-]+#[ ]*=[ ]*[ and | or | not |0-9|a-z|A-Z|\s|\-|\{|\}|\(|\)|]+$/';
		foreach ( $arraysplitmacroclasses as $rule ) {
			$rule = trim( $rule );
			if ( $rule ) {
				$r = preg_match( $structpattern, $rule );
				if ( $r !== 1 ) {
					$return = false;
					break;
				}
			}
			if ( ! $rule ) {
				break;
			}
			$bodyrule = explode( '=', $rule )[1];
			// Check that the opening and closing brackets are paired
			if ( substr_count( $bodyrule, '{' ) !== substr_count( $bodyrule, '}' ) ) {
				$return = false;
				break;
			}
			// Checking that the opening and closing parentheses are paired
			if ( substr_count( $bodyrule, '(' ) !== substr_count( $bodyrule, ')' ) ) {
				$return = false;
				break;
			}
			

			// Refine syntax checking for the rule body
			$start = '/^\s*(not\s*)?(\(\s*(not)?\s*(?!$))*' ;
			$class = '\{[-|0-9|a-z|A-Z|\s]+\}\s*' ;
			$operat = '(\)\s*)*((and|or)\s*(not)?\s*(?!$))?(\(\s*(?!$))*' ;
			$end = '$/' ;
			$bodypattern = $start . '(' . $class . $operat . ')*' . $end ;
			$r = preg_match( $bodypattern, $bodyrule );	
			if ( $r !== 1 ) {
				$return = false;
				break;
			}
	
		}
		return $return;
	}
	// ***************************************************************************************************
	// Bypass waiting for Woocommerce blocks Checkout bug to be corrected : Woocommerce is_checkout() not correctly working
	public static function cdi_is_checkout() {
		global $woocommerce;	
		$return = false ;
		if ( is_checkout() ) {
			$return = true ;
		}else{
                	$urlcheckout = home_url( get_option('woocommerce_checkout_page_endpoint') ) . '/' ;
                	if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				$referer = $_SERVER['HTTP_REFERER'] ;		
				if ( $referer == $urlcheckout ) {
					$return = true ;
				}
			}
		}
		if ($return != is_checkout()) {
			//cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Bug WC Blocks Checkout ( is_checkout() ) - Workaround build by CDI : -' . is_checkout() . '-' . $return . '-', 'tec' );
		}
		return $return ;
	}

}

class cdi_c_Evaluator {
	private array $_token_fns;

	private static function filter( $var ) {
		return $var !== null;
	}

	private static array $_find = array( '/(\()/', '/^/', '/(\))/', '/$/', '/(&&)/', '/(\|\|)/' );
	private static array $_repl = array( '$1 ( (', '( ( ', ') ) $1', ' ) )', ') $1 (', ') ) $1 ( (' );

	public function __construct() {
		$value_fns = array(
			'TRUE' => function( $carry ) {
				array_shift( $carry );
				return array( true, ...$carry );
			},
			'FALSE' => function( $carry ) {
				array_shift( $carry );
				return array( false, ...$carry );
			},
			'&&' => function ( $carry ) {
				$actor = array_shift( $carry );
				return array(
					function ( $next_carry ) use ( $actor ) {
						return $actor && $next_carry;
					},
					...$carry,
				);
			},
			'||' => function ( $carry ) {
				$actor = array_shift( $carry );
				return array(
					function ( $next_carry ) use ( $actor ) {
						return $actor || $next_carry;
					},
					...$carry,
				);
			},
			'!' => function ( $carry ) {
				array_shift( $carry );
				return array(
					function ( $next_carry ) {
										return ! $next_carry;
					},
					...$carry,
				);
			},
			'(' => function ( $carry ) {
				return array( null, $carry ); },
			')' => function ( $carry ) {
				$value = array_shift( $carry );
				$prev_carry = array_shift( $carry );
				return is_callable( $prev_carry ) ?
					array( $prev_carry( $value ), ...$carry ) :
					array_filter( array( $value ) + $prev_carry + $carry, 'self::filter' );
			},
		);

		$this->_token_fns = array(
			'boolean' => $value_fns,
			'NULL' => $value_fns,
			'object' => array( // PHP says callables/callback functions are objects
				'TRUE' => function ( $carry ) {
					$actor = array_shift( $carry );
					return array( $actor( true ), ...$carry );
				},
				'FALSE' => function ( $carry ) {
					$actor = array_shift( $carry );
					return array( $actor( false ), ...$carry );
				},
				'!' => function ( $carry ) {
					$actor = array_shift( $carry );
					return array(
						function ( $next_carry ) use ( $actor ) {
													return $actor( ! $next_carry );
						},
						...$carry,
					);
				},
				'(' => function ( $carry ) {
					return array( null, ...$carry ); },
			),
		);
	}

	private function cdi_reducer( $carry, $item ) {
		return $this->_token_fns[ ( gettype( $carry[0] ) ) ][ $item ]( $carry );
	}

	public function cdi_strictEval( $expression ) {
		$fixed_expr = preg_replace( static::$_find, static::$_repl, trim( $expression ) );
		$expr_array = preg_split( '/\s+/', $fixed_expr, -1, PREG_SPLIT_NO_EMPTY );
		return array_reduce( $expr_array, array( $this, 'self::cdi_reducer' ), array( null ) )[0];
	}
}


?>
