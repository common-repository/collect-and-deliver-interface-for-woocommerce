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
/* Cdi Gateway in a tab panel added in the woocommerce sidebar                          */
/****************************************************************************************/
class cdi_c_Gateway {
	/**
	 * Bootstraps the class and hooks required actions & filters.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::cdi_admin_enqueue_scripts_gateway' );
		add_action( 'admin_menu', __CLASS__ . '::register_cdi_submenu_page' );
		add_action( 'admin_init', __CLASS__ . '::cdi_label_voir' );
		add_action( 'admin_init', __CLASS__ . '::cdi_cn23_voir' );
		add_action( 'wp_ajax_cdi_ajax_gateway', __CLASS__ . '::cdi_ajax_gateway' );
	}

	public static function cdi_admin_enqueue_scripts_gateway( $hook_suffix ) {
		if ( ( $hook_suffix == 'woocommerce_page_passerelle-cdi' ) or ( $hook_suffix == 'plugins.php-cdi' ) ) {
			wp_enqueue_script( 'cdi_handle_js_admin_gateway', plugin_dir_url( __FILE__ ) . '../js/cdiadmingateway.js', array( 'jquery' ), $ver = false, $in_footer = true );
			$varjs = 'var cdiajaxurl = "' . admin_url( 'admin-ajax.php' ) . '" ; ';
			wp_add_inline_script( 'cdi_handle_js_admin_gateway', $varjs, $position = 'before' );
			wp_enqueue_script( 'cdi_handle_js_admin_preview', plugin_dir_url( __FILE__ ) . '../js/cdiadminpreview.js', array( 'jquery' ), $ver = false, $in_footer = true );
		}
	}

	/**
	 * Add a new Gateway tab to the WooCommerce sidebar.
	 */
	public static function register_cdi_submenu_page() {
		add_submenu_page( 'woocommerce', 'Passerelle CDI', 'Passerelle CDI', 'cdi_gateway', 'passerelle-cdi', __CLASS__ . '::cdi_submenu_page_callback' );
	}

	/**
	 * The CDI Gateway page here.
	 */
	public static function cdi_submenu_page_callback() {
		global $wpdb;
		global $woocommerce;
		global $message;
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'css-datepicker', plugin_dir_url( __FILE__ ) . '../css/css-datepicker.css' );
		?>
	  <div class="wrap">
		<?php // screen_icon( 'themes' ); // deprecated now so dont use ?>
		<form method="post" id="test">
		  <div style="display:inline-block;">
			<a style="display:inline-block;"> <?php cdi_c_Gateway_Bordereaux::cdi_bremise_open_button(); ?></a>
			<a style="display:inline-block;"> <?php cdi_c_Gateway_Debug::cdi_debug_open_button(); ?></a>
		  </div>
		</form>
		<div id="parcelsmanage">

		<?php include dirname( __FILE__ ) . '/CDI-Docs/CDI-Doc-Gateway.php'; ?>        
		
		<?php
		  $results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'cdi' );
		  $msgcountgateway = "<a id='cdi-count-gateway' style='color:black;'> " . count( $results ) . " </a><a id='cdi-textcount-gateway' style='color:black;'>" . __( ' parcel(s) in the gateway.', 'cdi' ) . '</a>';
		  $msgcountgateway = apply_filters( 'cdi_filterhtml_count_parcelsingateway', $msgcountgateway, count( $results ) );
		  echo wp_kses_post( $msgcountgateway );
		?>
   
		<div id="poststuff">
		  <div class="metabox-holder columns-2" id="post-body">
			<!-- ************************************************************************************************** -->
			<div id="post-body-content">
			  <form method="post" action="?page=<?php echo esc_js( esc_html( sanitize_text_field( $_GET['page'] ) ) ); ?>">

				 <?php self::cdi_parcels_remove(); ?>

				 <?php self::cdi_parcels_register(); ?>

				 <?php self::cdi_parcels_block(); ?>

				 <?php self::cdi_parcels_release(); ?>

				<?php
				if ( isset( $message ) ) {
					echo '<div style="padding: 5px;" class="updated"><p>' . esc_attr( $message ) . '</p></div>'; }
				?>

				<div id="outer" style="position: relative;">
				  <div id="inner" style="overflow: auto; max-height:70vh;">
					<table cellspacing="0" class="wp-list-table widefat fixed orderscdi">
					  <thead> <?php self::cdi_headfoot_table(); ?> </thead>
					  <tfoot> <?php self::cdi_headfoot_table(); ?> </tfoot>
					  <tbody id="the-list"> <?php self::cdi_body_table(); ?> </tbody>
					</table>
				  </div>
				</div>
				<br class="clear">
				<p>
				  <span style="float:left; margin-left:5px; font-weight:bold; color:#0085ba; font-size:2em; margin-top:-15px;">&#x21A7;</span>
				  <span style="font-weight:bold; color:#0085ba; font-size:2em;"> &#x27F6;</span>                  
				  <input name="cdi_block" type="submit" value="<?php _e( 'Block parcels', 'cdi' ); ?>" style="background-color:#0085ba; color:white; font-weight:bold;" title="<?php _e( 'Block parcels that you temporarily do not want to process in the cdi gateway.', 'cdi' ); ?>" />
				  <input name="cdi_release" type="submit" value="<?php _e( 'Release parcels', 'cdi' ); ?>" style="background-color:#0085ba; color:white; font-weight:bold;" title="<?php _e( 'Release parcels that you now want to process in the cdi gateway.', 'cdi' ); ?>" />
				  <input onclick="javascript:return confirm('<?php _e( 'Are you sure you want to delete ?', 'cdi' ); ?>');" name="cdi_remove" type="submit" value="<?php _e( 'Remove parcels', 'cdi' ); ?>" style="background-color:#0085ba; color:white; font-weight:bold;" title="<?php _e( 'Necessary when auto clean of parcels has not been set in settings. Your parcels can be remove at the end of a gateway session (i.e. after sending parcels to your carriers, collecting the tracking codes, and copying them in the gateway). Afterward, a new list of parcels can be prepared for a new gateway session.', 'cdi' ); ?>" /> 
				  <em></em>
				</p>
				<p>
				  <em></em>
				  <input name="cdi_register" type="submit" value="<?php _e( 'Register your changes', 'cdi' ); ?>" style="float: right; background-color:#0085ba; color:white; font-weight:bold;" title="<?php _e( 'Save your package tracking code changes.', 'cdi' ); ?>" />
				  <span style="float: right; margin-right:1em; font-weight:bold; color:#0085ba; font-size:2em; margin-top:-15px;"> &#x27F6;</span> 
				  <span style="float: right; margin-right:2em; font-weight:bold; ">Enregistrer tous vos codes de suivi modifiés de la Passerelle</span> 
				  <em></em>                 
				</p>
				
				<em></em>
				<p></p>
			  </form>
			  <em></em>                            

			  <!-- ************************************************************************************************** -->
			  <div class="meta-box-sortables">
			  </div>
			</div>
			<!-- ************************************************************************************************** -->
			<div class="postbox-container" id="postbox-container-1">
			  <!-- ************************************************************************************************** -->
			  <div class="meta-box-sortables">
				<div class="postbox">
				  <h3><span><?php _e( 'Submit your packages on hold (i.e. without yet a tracking code) in the gateway to your carriers :', 'cdi' ); ?></span></h3>

				  <div class="inside">
					<form method="post" id="cdi_gateway_manual" action="">
					  <input type="submit" name="cdi_gateway_manual" value="<?php _e( 'Manual', 'cdi' ); ?>" /> 
					  <img class="help_tip" title="<?php _e( 'A csv file will be exported. It can be printed to manage parcels to send to your carrier. It also can be used to activate an browser automation. It can be use as input for a carrier software. The parcel tracking codes will then have to be manually entered into the gateway panel.', 'cdi' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
					  <?php wp_nonce_field( 'cdi_manual_run', 'cdi_manual_run_nonce' ); ?> 
					</form>
					<p><em><?php _e( 'Export a csv file.', 'cdi' ); ?></em></p>
				  </div>
				  
				  <div class="inside">
					<form method="post" id="cdi_gateway_printlabel" target="_blank" action="">
					  <input type="submit" name="cdi_gateway_printlabel" value="<?php _e( 'Addresses printing for letters', 'cdi' ); ?>" /> 
					  <img class="help_tip" title="<?php _e( 'A pdf file with address labels will be exported. It can be printed for parcels or letters to send throught the carrier chosen. The parcel tracking codes will have to be manually entered into the gateway panel.', 'cdi' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
					  <?php wp_nonce_field( 'cdi_printlabel_run', 'cdi_printlabel_run_nonce' ); ?> 
					</form>
					<p><em><?php _e( 'Print address labels.', 'cdi' ); ?></em></p>
				  </div>                  
  
				  <div class="inside">
					<form method="post" id="cdi_gateway_colissimo" action="">
					  <input type="submit" name="cdi_gateway_colissimo" value="<?php _e( 'Colissimo', 'cdi' ); ?>" /> 
					  <img class="help_tip" title="<?php _e( 'The service will be executed in line with Colissimo Web service under soap protocol. A business contract with La Poste is needed. After printing of Colissimo labels, the parcel tracking codes will be automatically inserted into the gateway panel.', 'cdi' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
					  <?php wp_nonce_field( 'cdi_Colissimo_run_affranchissement', 'cdi_Colissimo_run_affranchissement_nonce' ); ?> 
					</form>
					<p><em><?php _e( 'Run the "Web Service d’Affranchissement Colissimo".', 'cdi' ); ?></em></p>
				  </div>

				  <div class="inside">
					<form method="post" id="cdi_gateway_mondialrelay" action="">
					  <input type="submit" name="cdi_gateway_mondialrelay" value="<?php _e( 'Mondial Relay', 'cdi' ); ?>" /> 
					  <img class="help_tip" title="<?php _e( 'The service will be executed in line with Mondial Relay Web service under soap protocol. A business contract with Mondial Relay is needed. After printing of Mondial Relay labels, the parcel tracking codes will be automatically inserted into the gateway panel.', 'cdi' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
					  <?php wp_nonce_field( 'cdi_mondialrelay_run_affranchissement', 'cdi_mondialrelay_run_affranchissement_nonce' ); ?> 
					</form>
					<p><em><?php _e( 'Run the "Web Service d’Affranchissement Mondial Relay".', 'cdi' ); ?></em></p>
				  </div>            
				  
				  <div class="inside">
					<form method="post" id="cdi_gateway_ups" action="">
					  <input type="submit" name="cdi_gateway_ups" value="<?php _e( 'UPS', 'cdi' ); ?>" /> 
					  <img class="help_tip" title="<?php _e( 'The service will be executed in line with UPS Web services under soap or xml protocols. A business contract with UPS is needed. After printing of UPS labels, the parcel tracking codes will be automatically inserted into the gateway panel.', 'cdi' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
					  <?php wp_nonce_field( 'cdi_ups_run_affranchissement', 'cdi_ups_run_affranchissement_nonce' ); ?> 
					</form>
					<p><em><?php _e( 'Run the "Web Service d’Affranchissement UPS".', 'cdi' ); ?></em></p>
				  </div>
				  
				  <div class="inside">
					<form method="post" id="cdi_gateway_collect" action="">
					  <input type="submit" name="cdi_gateway_collect" value="<?php _e( 'Collect', 'cdi' ); ?>" /> 
					  <img class="help_tip" title="<?php _e( 'The service will be executed with Clieck & Collect services. After printing of Collect labels, the parcel tracking codes will be automatically inserted into the gateway panel.', 'cdi' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
					  <?php wp_nonce_field( 'cdi_collect_run_affranchissement', 'cdi_collect_run_affranchissement_nonce' ); ?> 
					</form>
					<p><em><?php _e( 'Run the "Click & Collect Service".', 'cdi' ); ?></em></p>
				  </div>                

				  <div class="inside">
					<form method="post" id="cdi_gateway_custom" action="">
					  <input type="submit" name="cdi_gateway_custom" value="<?php echo cdi_c_Function::cdi_get_libelle_carrier( 'notcdi' ); ?>" /> 
					  <img class="help_tip" title="<?php _e( 'A custom service will be executed. The filter $cdi_tracking = apply_filters (\'cdi_custom_gateway_exec\', $cdi_tracking=false , $cdi_nbrorderstodo , $cdi_rowcurrentorder, $array_for_carrier) is used. $array_for_carrier contains the datas to process with your software carrier. The parcel tracking code is returned in $cdi_tracking and will be automatically updated into the gateway panel. $cdi_nbrorderstodo and $cdi_rowcurrentorder are respectively the number of orders to process and the rank of the current order in process.', 'cdi' ); ?>"  src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
					  <?php wp_nonce_field( 'cdi_custom_run', 'cdi_custom_run_nonce' ); ?> 
					</form>
					<p><em><?php _e( 'Run your custom computer program through a filter.', 'cdi' ); ?></em></p>
				  </div>

				</div>
				
			  </div>
			  <!-- ************************************************************************************************** -->
			</div>
			<!-- ************************************************************************************************** -->
		  </div>
		  <br class="clear">
		</div>
	  </div>
	  <!-- End of div id="parcelsmanage" -->
	  </div> 
		<?php
		cdi_c_Gateway_Bordereaux::bremise_manage();
		cdi_c_Gateway_Debug::debug_manage();
	}

	/**
	 * Parcels blocking
	 */
	public static function cdi_parcels_block() {
		global $wpdb;
		global $woocommerce;
		global $message;
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_block'] ) ) {
			if ( isset( $_GET['rem'] ) ) {
				$_POST['rem'][] = sanitize_text_field( $_GET['rem'] );
			}
			$count = 0;
			if ( isset( $_POST['rem'] ) && is_array( $_POST['rem'] ) ) {
				foreach ( $_POST['rem'] as $id ) {
					$results = $wpdb->update( $wpdb->prefix . 'cdi', array( 'cdi_status' => 'close' ), array( 'ID' => $id ) );
					$count++;
				}
			}
			$message = $count . __( ' parcel(s) have been freezed.', 'cdi' );
		}
	}
	/**
	 * Parcels release
	 */
	public static function cdi_parcels_release() {
		global $wpdb;
		global $woocommerce;
		global $message;
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_release'] ) ) {
			if ( isset( $_GET['rem'] ) ) {
				$_POST['rem'][] = sanitize_text_field( $_GET['rem'] );
			}
			$count = 0;
			if ( isset( $_POST['rem'] ) && is_array( $_POST['rem'] ) ) {
				foreach ( $_POST['rem'] as $id ) {
					$results = $wpdb->update( $wpdb->prefix . 'cdi', array( 'cdi_status' => 'open' ), array( 'ID' => $id ) );
					$count++;
				}
			}
			$message = $count . __( ' parcel(s) have been unfreezed.', 'cdi' );
		}
	}
	/**
	 * Parcels register
	 */
	public static function cdi_parcels_register() {
		global $wpdb;
		global $woocommerce;
		global $message;
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_register'] ) ) {
			if ( isset( $_GET['parcelrow'] ) ) {
				$_POST['parcelrow'][] = sanitize_text_field( $_GET['parcelrow'] );
			}
			$count = 0;
			if ( isset( $_POST['parcelrow'] ) && is_array( $_POST['parcelrow'] ) ) {
				foreach ( $_POST['parcelrow'] as $id => $track ) {			
					$track = preg_replace( '/[^A-Z0-9]/', '', strtoupper( $track ) );
					$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . "cdi where id like '" . esc_sql( $id ) . "' " );					
					if ( count( $results ) ) {					
						if ( $results[0]->cdi_tracking !== $track ) {
							$x = $wpdb->update( $wpdb->prefix . 'cdi', array( 'cdi_tracking' => $track ), array( 'ID' => $id ) );
							$order_id = $results[0]->cdi_order_id;
							self::cdi_synchro_gateway_to_order( $order_id );
							$count++;
						}
					}
				}
				$message = $count . __( ' tracking code(s) have been changed.', 'cdi' );
			}
		}
	}
	/**
	 * Parcels remove
	 */
	public static function cdi_parcels_remove() {
		global $wpdb;
		global $woocommerce;
		global $message;
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_remove'] ) ) {
			if ( isset( $_GET['rem'] ) ) {
				$_POST['rem'][] = sanitize_text_field( $_GET['rem'] );
			}
			$count = 0;
			if ( isset( $_POST['rem'] ) && is_array( $_POST['rem'] ) ) {
				foreach ( $_POST['rem'] as $id ) {
					$wpdb->query( 'delete from ' . $wpdb->prefix . "cdi where id = '" . esc_sql( $id ) . "' limit 1" );
					$count++;
				}
				$message = $count . __( ' parcels have been removed successfully.', 'cdi' );
			}
		}
	}
	/**
	 * Head and Foot.
	 */
	public static function cdi_headfoot_table() {
		?>
		  <tr>
			<th class="manage-column column-cb check-column" id="cb" scope="col" style=""><input type="checkbox"></th>
			<th class="manage-column column-orderid" id="cdi-order-id" scope="col" style="width:10%;"><?php _e( 'Parcel', 'cdi' ); ?><span class="sorting-indicator"></span></th>
			<th class="manage-column column-preview" id="cdi-order-order" scope="col" style="width:6%;"><?php _e( 'Order', 'cdi' ); ?><span class="sorting-indicator"></span></th>
			<th class="manage-column column-name" id="cdi-name" scope="col" style="width:20%;"><span><?php _e( 'Destination', 'cdi' ); ?></span><span class="sorting-indicator"></span></th>
			<th class="manage-column column-address" id="cdi-address" scope="col" style="width:8%;" title="<?php _e( 'Shipping address is only for home shippings. For pickup shippings the pickup address is displayed elsewhere.', 'cdi' ); ?>"><span><?php _e( 'Shipping address', 'cdi' ); ?></span><span class="sorting-indicator"></span></th>
			<th class="manage-column column-trackingcode" id="cdi-tracking" scope="col" style=""><span><?php _e( 'Tracking code', 'cdi' ); ?></span><span class="sorting-indicator"></span></th>
			<th class="manage-column column-label" id="cdi-label" scope="col" style="width:6%;"><span><?php _e( 'Label', 'cdi' ); ?></span><span class="sorting-indicator"></span></th>
<th class="manage-column column-label" id="cdi-url" scope="col" style="width:2%;"><span><?php _e( 'Url', 'cdi' ); ?></span><span class="sorting-indicator"></span></th>            
			<th class="manage-column column-cn23" id="cdi-cn23" scope="col" style="width:6%;"><span>Cn23</span><span class="sorting-indicator"></span></th>
			<th class="manage-column column-carrier" id="cdi-carrier" scope="col" style="width:7%;"><span><?php _e( 'Carrier', 'cdi' ); ?></span><span class="sorting-indicator"></span></th>            
		  </tr>
		<?php
	}
	/**
	 * Body of table
	 */
	public static function cdi_body_table() {
		global $wpdb;
		global $woocommerce;
		global $message;
		$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'cdi' );
		function cmp( $a, $b ) {
			$r = -1;
			if ( $b->cdi_order_id > $a->cdi_order_id ) {
				$r = 1;
			} return $r; }
		usort( $results, 'cmp' );
		if ( count( $results ) < 1 ) {
			echo '<tr class="no-items"><td colspan="3" class="colspanchange">' . __( 'No parcel have been registered in the gateway.', 'cdi' ) . '</td></tr>';
		} else {
			$results = apply_filters( 'cdi_filterarray_gateway_sortresults', $results );
			$arrhtmljs = array();
			$arrhtmlshipadjs = array();
			$ajaxurl = admin_url( 'admin-ajax.php' );
			foreach ( $results as $row ) {
				if ( $row->cdi_status == 'close' ) {
					$color = '#aaaaaa';
				} else {
					$color = '#ffffff';
				}
				if ( wc_get_order( $row->cdi_order_id ) != false ) { // check if order exist	
					$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $row->cdi_order_id );
					$display = $array_for_carrier['shipping_last_name'] . ' ' . $array_for_carrier['shipping_first_name'] . ' (' . $array_for_carrier['shipping_country'] . ') | ' .
					 'Weight: ' . $array_for_carrier['parcel_weight'] . 'g | ' .
					 ' ' . 'shipping_company: ' . $array_for_carrier['shipping_company'] . ' | ' .
					 ' ' . 'shipping_address_1: ' . $array_for_carrier['shipping_address_1'] . ' | ' .
					 ' ' . 'shipping_address_2: ' . $array_for_carrier['shipping_address_2'] . ' | ' .
					 ' ' . 'shipping_postcode: ' . $array_for_carrier['shipping_postcode'] . ' | ' .
					 ' ' . 'shipping_city_state: ' . $array_for_carrier['shipping_city_state'] . ' | ' .
					 ' ' . 'billing_phone: ' . $array_for_carrier['billing_phone'] . ' | ' .
					 ' ' . 'billing_email: ' . $array_for_carrier['billing_email'] . ' | ' .
					 ' ' . 'cdi_meta_typeparcel: ' . $array_for_carrier['parcel_type'] . ' | ' .
					 ' ' . 'cdi_meta_signature: ' . $array_for_carrier['signature'] . ' | ' .
					 ' ' . 'cdi_meta_additionalcompensation: ' . $array_for_carrier['additional_compensation'] . ' | ' .
					 ' ' . 'cdi_meta_amountcompensation: ' . $array_for_carrier['compensation_amount'] . ' | ' .
					 ' ' . 'cdi_meta_typereturn: ' . $array_for_carrier['return_type'] . ' | ' .
					 ' ' . 'cdi_meta_productCode: ' . $array_for_carrier['product_code'] . ' | ' .
					 ' ' . 'cdi_meta_pickupLocationId: ' . $array_for_carrier['pickup_Location_id'] . ' | ' .
					 ' ';
					$displayorder = $array_for_carrier['shipping_last_name'] . ' ' . $array_for_carrier['shipping_first_name'] . ' (' . $array_for_carrier['shipping_country'] . ') ' . $array_for_carrier['parcel_weight'] . 'g ' . substr( $array_for_carrier['order_date'], 0, 10 );
					$displayorder = apply_filters( 'cdi_filterstring_gateway_displayorder', $displayorder, $array_for_carrier );

					$arrhtmljs[ $row->cdi_order_id ] = self::cdi_preview( $row->cdi_order_id );
					$arrhtmlshipadjs[ $row->cdi_order_id ] = self::cdi_checkad( $row->cdi_order_id );
					$imgmetabox = '<img src="' . plugins_url( 'images/iconmetabox.png', dirname( __FILE__ ) ) . '">';
					$imgorder = '<img src="' . plugins_url( 'images/iconorder.png', dirname( __FILE__ ) ) . '">';
					$imgcheck = '<img src="' . plugins_url( 'images/iconcheckad.png', dirname( __FILE__ ) ) . '">';
					$orderid = esc_js( esc_html( $row->cdi_order_id ) );
					$order = wc_get_order( $orderid ) ;
					$ordernumber = $order->get_order_number();
					$displayreforder = $orderid . '(' . $ordernumber . ')';
					$carrier = $order->get_meta( '_cdi_meta_carrier' );
					$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
					echo '<tr style="background-color:' . esc_attr( $color ) . ';"> 
                <th class="check-column" style="padding:5px 0 2px 0"><input type="checkbox" name="rem[]" value="' . esc_js( esc_html( $row->id ) ) . '"></th>
                <td><a class="cdi-preview-metabox" name="cdi_preview_metabox' . esc_attr( $row->cdi_order_id ) . '" title="Aperçu de cette CDI Metabox" > ' . wp_kses_post( $imgmetabox ) . wp_kses_post( $displayreforder ) . '</a></td>
                <td><a class="cdi-preview-order" name="cdi_preview_' . esc_attr( $row->cdi_order_id ) . '" title="Aperçu de cette commande" > ' . wp_kses_post( $imgorder ) . wp_kses_post( $ordernumber ) . ' </a></td>
                <td>' . esc_attr( $displayorder ) . ' </td>
                <td><a class="cdi-checkad" name="cdi_checkad_' . esc_attr( $row->cdi_order_id ) . '" title="Vérification adresse de livraison de cette commande / ce colis. Elle concerne uniquement les expéditions à domicile. Pour les envois en point relai, le lieu de retrait est affichée ailleurs en complément." > ' . wp_kses_post( $imgcheck ) . wp_kses_post( $displayreforder ) . ' </a></td>
                <td><input name="parcelrow[' . esc_js( esc_html( $row->id ) ) . ']" style="width:95%" value="' . esc_js( esc_html( $row->cdi_tracking ) ) . '"/> </td>';

					if ( $row->cdi_order_id && $order->get_meta( '_cdi_meta_exist_uploads_label' ) == true ) {
						echo '<td style="overflow:hidden; white-space:nowrap; text-overflow: ellipsis;"><form method="post" id="cdi_label_voir_' . esc_attr( $row->cdi_order_id ) . '" action="" style="display:inline-block;"><input type="hidden" name="cdi_label_voir_post" value="' . esc_attr( $row->cdi_order_id ) . '"><input type="submit" name="cdi_label_voir" value="Voir"  title="Print label" /></form></td>';
					} else {
						echo '<td></td>';
					}

					if ( $row->cdi_order_id && $row->cdi_hreflabel ) {
						echo '<td><a style="font-size: 20px;" onmouseover="this.style.color=\'red\';" onmouseout="this.style.color=\'\';" href="' . esc_js( esc_html( $row->cdi_hreflabel ) ) . '" onclick="window.open(this.href); return false;">' . '@' . '</a></td>';
					} else {
						echo '<td></td>';
					}

					if ( $row->cdi_order_id && $order->get_meta( '_cdi_meta_exist_uploads_cn23' ) == true ) {
						echo '<td style="overflow:hidden; white-space:nowrap; text-overflow: ellipsis;"><form method="post" id="cdi_cn23_voir_' . esc_attr( $row->cdi_order_id ) . '" action="" style="display:inline-block;"><input type="hidden" name="cdi_cn23_voir_post" value="' . esc_attr( $row->cdi_order_id ) . '"><input type="submit" name="cdi_cn23_voir" value="Voir"  title="Print cn23" /></form></td>';
					} else {
						echo '<td></td>';
					}

					echo '<td>' . esc_attr( cdi_c_Function::cdi_get_libelle_carrier( $carrier ) ) . ' </td></tr>';

				} else { // order does not exist so clean the gateway
					$wpdb->query( 'delete from ' . $wpdb->prefix . "cdi where cdi_order_id = '" . $row->cdi_order_id . "' limit 1" );
				} // end test order exist
			} // end foreach
			$varjs = "var ajaxurl = '" . admin_url( 'admin-ajax.php' ) . "' ; " .
				   'var cdiarrhtmljs = ' . json_encode( $arrhtmljs ) . ' ; ' .
				   'var cdiarrhtmlshipadjs = ' . json_encode( $arrhtmlshipadjs ) . ' ; ';
			update_option( 'cdi_handle_js_admin_preview' . '_' . get_current_user_id(), $varjs );
			$varjs = get_option( 'cdi_handle_js_admin_preview' . '_' . get_current_user_id() );
			wp_add_inline_script( 'cdi_handle_js_admin_preview', $varjs, $position = 'before' );
		}
	}

	/**
	 * Process of Ajax requests from Gateway.
	 */
	public static function cdi_ajax_gateway() {
		global $woocommerce;
		$endpoint = 'https://api.laposte.fr/controladresse/v1/adresses';
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['case'] ) ) {
			$case = sanitize_text_field( $_POST['case'] );
			switch ( $case ) {
				case '1':
					$reponse = '<br><br>';
					if ( sanitize_text_field( $_POST['address']['pa'] ) !== 'France' ) {
						$reponse .= '<strong>Erreur : </strong>Vérification non disponible pour cette destination.<br>';
					}
					if ( null == get_option( 'cdi_o_settings_apikeylaposte' ) ) {
						$reponse .= '<br><strong>Erreur : </strong>Service nécessitant une clé API de La Poste.<br>';
					}
					if ( strlen( $reponse ) > 8 ) {
						echo wp_json_encode(
							array(
								'0' => 'nok',
								'1' => $reponse,
							)
						);
						wp_die();
					}
					$rawaddress = sanitize_text_field( $_POST['address']['l1'] ) . ' ' .
						  sanitize_text_field( $_POST['address']['l2'] ) . ' ' .
						  sanitize_text_field( $_POST['address']['l3'] ) . ' ' .
						  sanitize_text_field( $_POST['address']['l4'] ) . ' ' .
						  sanitize_text_field( $_POST['address']['cp'] ) . ' ' .
						  sanitize_text_field( $_POST['address']['vi'] );
					$request  = 'q=' . $rawaddress;
					$headers  = array(
						'X-Okapi-Key' => get_option( 'cdi_o_settings_apikeylaposte' ),
						'Content-Type' => 'application/json',
						'Accept' => 'application/json',
					);
					$responseapi = wp_remote_get(
						$endpoint . '?' . $request,
						array(
							'timeout' => 70,
							'headers' => $headers,
						)
					);
					$responseapi_code = $responseapi['response']['code'];
					$responseapi_message = $responseapi['response']['message'];
					if ( $responseapi_message !== 'OK' ) {
						echo wp_json_encode(
							array(
								'0' => 'nok',
								'1' => '<strong>Erreur API : </strong>' . $responseapi['response']['message'] . '<br>',
							)
						);
						wp_die();
					}
					echo wp_json_encode(
						array(
							'0' => 'ok',
							'1' => $responseapi['body'],
						)
					);
					wp_die();
					break;
				case '2':
					$request  = sanitize_text_field( $_POST['code'] );
					$headers  = array(
						'X-Okapi-Key' => get_option( 'cdi_o_settings_apikeylaposte' ),
						'Content-Type' => 'application/json',
						'Accept' => 'application/json',
					);
					$responseapi = wp_remote_get(
						$endpoint . '/' . $request,
						array(
							'timeout' => 70,
							'headers' => $headers,
						)
					);
					$responseapi_code = $responseapi['response']['code'];
					$responseapi_message = $responseapi['response']['message'];
					if ( $responseapi_message !== 'OK' ) {
						echo wp_json_encode(
							array(
								'0' => 'nok',
								'1' => '<strong>Erreur API : </strong>' . $responseapi['response']['message'] . '<br>',
							)
						);
						  wp_die();
					}
					$objaddress = json_decode( $responseapi['body'] );

					$newadrstructure = array();
					$newadrstructure['l1'] = $objaddress->numeroVoie . ' ' . $objaddress->libelleVoie;
					$newadrstructure['l2'] = $objaddress->pointRemise;
					$newadrstructure['l3'] = $objaddress->destinataire;
					$newadrstructure['l4'] = $objaddress->lieuDit;
					$newadrstructure['cp'] = $objaddress->codePostal;
					if ( $objaddress->codeCedex ) {
						$newadrstructure['cp'] = $objaddress->codeCedex;
					}
					$newadrstructure['vi'] = $objaddress->commune;
					$newadrstructure['cpvi'] = $newadrstructure['cp'] . ' ' . $newadrstructure['vi'];

					$newadrhtml = '<br>';
					foreach ( $newadrstructure as $key => $label ) {
						if ( $label && $key !== 'cp' && $key !== 'vi' ) {
							$newadrhtml .= '<br>' . $label;
						}
					}
					$newadrhtml .= '<br>FRANCE';
					echo wp_json_encode(
						array(
							'0' => 'ok',
							'1' => $newadrstructure,
							'2' => $newadrhtml,
						)
					);
					wp_die();
					break;
				case '3':
					$order = wc_get_order( $_POST['order'] );					
					$order->set_shipping_address_1( cdi_c_Function::cdi_sanitize_voie( sanitize_text_field( $_POST['newadr']['l1'] ) ) );
					$order->set_shipping_address_2( cdi_c_Function::cdi_sanitize_voie( sanitize_text_field( $_POST['newadr']['l2'] ) ) );
					$order->update_meta_data( '_shipping_address_3', cdi_c_Function::cdi_sanitize_voie( sanitize_text_field( $_POST['newadr']['l3'] ) ) );
					$order->update_meta_data( '_shipping_address_4', cdi_c_Function::cdi_sanitize_voie( sanitize_text_field( $_POST['newadr']['l4'] ) ) );
					$order->set_shipping_postcode( cdi_c_Function::cdi_sanitize_voie( sanitize_text_field( $_POST['newadr']['cp'] ) ) );
					$order->set_shipping_city( cdi_c_Function::cdi_sanitize_voie( sanitize_text_field( $_POST['newadr']['vi'] ) ) );
					$order->save();
					echo wp_json_encode(
						array(
							'0' => 'ok',
						)
					);
					wp_die();
					break ;
				case '4':
					$order_id = sanitize_text_field( $_POST['code'] ) ;
					$order = wc_get_order( $order_id );
					$cdi_status = $order->get_meta( '_cdi_meta_status' );
					$lib_cdi_status = str_replace( array( 'waiting', 'deposited', 'intruck' ), array( __( 'Waiting', 'cdi' ), __( 'Deposited', 'cdi' ), __( 'Intruck', 'cdi' ) ), $cdi_status );

					$html = '';

					$html .= '<p>(' . __( 'More details in CDI Metabox', 'cdi' ) . ')</p><p style="clear:both"></p>';
					$html .= '<div class="cdi-tracking-box">';
					$cdi_status = $order->get_meta( '_cdi_meta_status' );

					$cdi_meta_departure = $order->get_meta( '_cdi_meta_departure' );
					$html .= '<p><a>' . __( 'From : ', 'cdi' ) . esc_attr( $cdi_meta_departure ) . '</a><br>';
					$shipping_country = $order->get_shipping_country();
					$html .= '<a>' . __( 'To : ', 'cdi' ) . esc_attr( $shipping_country ) . '</a><br>';
					$method_name = $order->get_meta( '_cdi_meta_shippingmethod_name' );

					$carrier = $order->get_meta( '_cdi_meta_carrier' );
					$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
					$carrierlabel = cdi_c_Function::cdi_get_libelle_carrier( $carrier );
					$html .= '<a>' . __( 'shipping request: ', 'cdi' ) . esc_attr( $method_name ) . __( ' - Carrier used: ', 'cdi' ) . $carrierlabel . '</a><br>';

					$items_chosen = cdi_c_Function::cdi_get_items_chosen( $order );
					foreach ( $items_chosen as $item ) {
						$product_id = $item['variation_id'];
						if ( $product_id == 0 ) { // No variation for that one
							$product_id = $item['product_id'];
						}
						$product = wc_get_product( $product_id );
						$artdesc = $product->get_name();
						$html .= '<a style="margin:2px;"> => ' . esc_attr( $artdesc ) . ' x ' . esc_attr( $item['qty'] ) . '</a><br>';
					}
					$html .= '</p><p style="clear:both"></p>';

					$html .= '<div style="background-color:#eeeeee; color:#000000; width:100%;">Tracking zone</div><p style="clear:both"></p><p>';
					$cdi_meta_tracking = $order->get_meta( '_cdi_meta_tracking' );
					$html .= '<a>' . __( 'Tracking code : ', 'cdi' ) . esc_attr( $cdi_meta_tracking ) . '</a><br>';
					if ( $cdi_status == 'intruck' && $cdi_meta_tracking ) {
						$html .= '<a style="color:black;">' . esc_attr( cdi_c_Function::cdi_get_whereis_parcel( $order_id, $cdi_meta_tracking ) ) . '</a><br>';
					}
					$cdi_parcelNumberPartner = $order->get_meta( '_cdi_meta_parcelNumberPartner' );
					if ( $cdi_parcelNumberPartner ) {
						$html .= '<a>' . __( 'Partner number : ', 'cdi' ) . esc_attr( $cdi_parcelNumberPartner ) . '</a><br';
					}
					$html .= '</p><p style="clear:both"></p>';

					$html .= '<div style="background-color:#eeeeee; color:#000000; width:100%;">' . __( 'Parcel parameters', 'cdi' ) . '</div><p style="clear:both"></p><p>';
					$cdi_meta_typeparcel = $order->get_meta( '_cdi_meta_typeparcel' );
					$html .= '<a>' . __( 'Parcel : ', 'cdi' ) . esc_attr( $cdi_meta_typeparcel ) . '</a><br>';
					$cdi_meta_parcelweight = $order->get_meta( '_cdi_meta_parcelweight' );
					$html .= '<a>' . __( 'Weight : ', 'cdi' ) . esc_attr( $cdi_meta_parcelweight ) . '</a><br>';
					$html .= '</p><p style="clear:both"></p>';

					$html .= '<div style="background-color:#eeeeee; color:#000000; width:100%;">' . __( 'Optional services', 'cdi' ) . '</div><p style="clear:both"></p><p>';
					$cdi_meta_signature = $order->get_meta( '_cdi_meta_signature' );
					$html .= '<a>' . __( 'Signature : ', 'cdi' ) . esc_attr( $cdi_meta_signature ) . '</a><br>';
					$cdi_meta_additionalcompensation = $order->get_meta( '_cdi_meta_additionalcompensation' );
					$html .= '<a>' . __( 'Compensation + : ', 'cdi' ) . esc_attr( $cdi_meta_additionalcompensation ) . '</a><br>';
					$cdi_meta_amountcompensation = $order->get_meta( '_cdi_meta_amountcompensation' );
					$html .= '<a>' . __( 'Amount : ', 'cdi' ) . esc_attr( $cdi_meta_amountcompensation ) . '</a><br>';
					$cdi_meta_returnReceipt = $order->get_meta( '_cdi_meta_returnReceipt' );
					$html .= '<a>' . __( 'Avis réception : ', 'cdi' ) . esc_attr( $cdi_meta_returnReceipt ) . '</a><br>';
					$cdi_meta_typereturn = $order->get_meta( '_cdi_meta_typereturn' );
					$html .= '<a>' . __( 'Return : ', 'cdi' ) . esc_attr( $cdi_meta_typereturn ) . '</a><br>';
					$cdi_meta_ftd = $order->get_meta( '_cdi_meta_ftd' );
					$html .= '<a>' . __( 'ftd OM : ', 'cdi' ) . esc_attr( $cdi_meta_ftd ) . '</a><br>';
					$html .= '</p><p style="clear:both"></p>';

					$html .= '<div style="background-color:#eeeeee; color:#000000; width:100%;">' . __( 'Customer shipping settings', 'cdi' ) . '</div></p><p>';
					$cdi_meta_productCode = $order->get_meta( '_cdi_meta_productCode' );
					$html .= '<a>' . __( 'Forced product code : ', 'cdi' ) . esc_attr( $cdi_meta_productCode ) . '</a><br>';
					$cdi_meta_pickupLocationId = $order->get_meta( '_cdi_meta_pickupLocationId' );
					$html .= '<a>' . __( 'Pickup location id : ', 'cdi' ) . esc_attr( $cdi_meta_pickupLocationId ) . '</a><br>';
					$cdi_meta_pickupLocationlabel = $order->get_meta( '_cdi_meta_pickupLocationlabel' );
					$html .= '<a>' . __( 'Location : ', 'cdi' ) . esc_attr( $cdi_meta_pickupLocationlabel ) . '</a><br>';
					$html .= '</p><p style="clear:both"></p>';

					$html .= '<div style="background-color:#eeeeee; color:#000000; width:100%;">' . __( 'CN23 parameters', 'cdi' ) . '</div><p style="clear:both"></p><p>';
					$cdi_meta_cn23_shipping = $order->get_meta( '_cdi_meta_cn23_shipping' );
					$html .= '<a>' . __( 'CN23 transport : ', 'cdi' ) . esc_attr( $cdi_meta_cn23_shipping ) . '</a><br>';
					$cdi_meta_cn23_category = $order->get_meta( '_cdi_meta_cn23_category' );
					$html .= '<a>' . __( 'CN23 category : ', 'cdi' ) . esc_attr( $cdi_meta_cn23_category ) . '</a><br>';
					$html .= '</p><p style="clear:both"></p>';

					$html .= '<div style="background-color:#eeeeee; color:#000000; width:100%;">' . __( 'Parcel return', 'cdi' ) . '</div><p style="clear:both"></p><p>';
					$cdi_meta_nbdayparcelreturn = $order->get_meta( '_cdi_meta_nbdayparcelreturn' );
					$html .= '<a>' . __( 'Return days : ', 'cdi' ) . esc_attr( $cdi_meta_nbdayparcelreturn ) . '</a><br>';
					$cdi_meta_parcelnumber_return = $order->get_meta( '_cdi_meta_parcelnumber_return' );
					$html .= '<a>' . __( 'Return tracking code : ', 'cdi' ) . esc_attr( $cdi_meta_parcelnumber_return ) . '</a><br>';
					$html .= '</p><p style="clear:both"></p>';

					$html .= '</div>';

					echo wp_json_encode(
						array(
							'0' => 'ok',
							'1' => $html,
							'2' => $lib_cdi_status,
						)
					);
					wp_die();
					break;
				default:
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, sanitize_text_field( $_POST['case'] ), 'tec' );
					break;
			}
		}
	}

	/**
	 * View and print label.
	 */
	public static function cdi_label_voir() {
		if ( isset( $_POST['cdi_label_voir'] ) ) {
			global $woocommerce;
			global $wpdb;
			$order_id = sanitize_text_field( $_POST['cdi_label_voir_post'] );
			if ( current_user_can( 'cdi_gateway' ) ) {
				$cdi_loclabel = cdi_c_Function::cdi_uploads_get_contents( $order_id, 'label' );
				if ( $cdi_loclabel ) {
					$cdi_loclabel_pdf = base64_decode( $cdi_loclabel );
					$out = fopen( 'php://output', 'w' );
					$thepdffile = 'Label-' . $order_id . '-' . date( 'YmdHis' ) . '.pdf';
					header( 'Content-Type: application/pdf' );
					header( 'Content-Disposition: attachment; filename=' . $thepdffile );
					fwrite( $out, $cdi_loclabel_pdf );
					fclose( $out );
					die();
				}
			} // End current_user_can
		}
	}

	/**
	 * View and print cn23.
	 */
	public static function cdi_cn23_voir() {
		if ( isset( $_POST['cdi_cn23_voir'] ) ) {
			global $woocommerce;
			global $wpdb;
			$order_id = sanitize_text_field( $_POST['cdi_cn23_voir_post'] );
			if ( current_user_can( 'cdi_gateway' ) ) {
				$cdi_loccn23 = cdi_c_Function::cdi_uploads_get_contents( $order_id, 'cn23' );
				if ( $cdi_loccn23 ) {
					$cdi_loccn23_pdf = base64_decode( $cdi_loccn23 );
					$out = fopen( 'php://output', 'w' );
					$thepdffile = 'cn23-' . $order_id . '-' . date( 'YmdHis' ) . '.pdf';
					header( 'Content-Type: application/pdf' );
					header( 'Content-Disposition: attachment; filename=' . $thepdffile );
					fwrite( $out, $cdi_loccn23_pdf );
					fclose( $out );
					die();
				}
			} // End current_user_can
		}
	}
	/**
	 * Preview the order.
	 */
	public static function cdi_preview( $order_id ) {
		$html = '';
		global $woocommerce;
		global $wpdb;
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$carrier = $order->get_meta('_cdi_meta_carrier' );
			$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
			$carrierlabel = cdi_c_Function::cdi_get_libelle_carrier( $carrier );
			include_once( WP_PLUGIN_DIR . '/woocommerce/includes/admin/list-tables/class-wc-admin-list-table-orders.php' );
			$result = WC_Admin_List_Table_Orders::order_preview_get_order_details( $order );
			$html = '<header style="display:inline-block;">	<h1 style="display:inline-block;">' . 'Aperçu Client/Commande : ' . esc_attr( $result['order_number'] ) . '</h1> <mark style="display:inline-block;"><span>' . esc_attr( $result['status_name'] ) . '</span></mark> </header>';
			$html .= '<div><h2>' . __( 'Billing details', 'woocommerce' ) . '</h2>' . $result['formatted_billing_address'] . '<p><strong>' . __( 'Email', 'woocommerce' ) . '</strong><a>' . esc_attr( $result['data']['billing']['email'] ) . '</a></p><p><strong>' . __( 'Phone', 'woocommerce' ) . '</strong><a>' . esc_attr( $result['data']['billing']['phone'] ) . '</a></p><p><strong>' . __( 'Payment via', 'cdi' ) . ' </strong>' . esc_attr( $result['payment_via'] ) . '</p></div>';
			$html .= '<div><h2>' . __( 'Shipping details', 'woocommerce' ) . '</h2>' . $result['formatted_shipping_address'] . '<p>' . __( 'shipping request: ', 'cdi' ) . esc_attr( $result['shipping_via'] ) . __( ' - Carrier used: ', 'cdi' ) . $carrierlabel . '</p></div>';
			$html .= '<div><p><strong>' . __( 'Customer note', 'cdi' ) . ' </strong>' . esc_attr( $result['data']['customer_note'] ) . '</p></div>';
			$html .= $result['item_html'];
		}
		return $html;
	}

	public static function cdi_checkad( $order_id ) {
		global $woocommerce;
		$order = wc_get_order( $order_id );		
		$cdi_status = $order->get_meta( '_cdi_meta_status' );
		$lib_cdi_status = str_replace( array( 'waiting', 'deposited', 'intruck' ), array( __( 'Waiting', 'cdi' ), __( 'Deposited', 'cdi' ), __( 'Intruck', 'cdi' ) ), $cdi_status );
		$country_code = $order->get_shipping_country() ;
		if ( !$country_code ) {
			$country_code = 'FR' ;
		}
		$return = array(
			'st' => $lib_cdi_status,
			'l1' => $order->get_shipping_address_1(),
			'l2' => $order->get_shipping_address_2(),
			'l3' => $order->get_meta( '_shipping_address_3' ),
			'l4' => $order->get_meta( '_shipping_address_4' ),
			'cp' => $order->get_shipping_postcode(),
			'vi' => $order->get_shipping_city(),
			'pa' => WC()->countries->countries[ $country_code ],
		);
		return $return;
	}

	public static function cdi_synchro_gateway_to_order( $order_id ) {
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . "cdi where cdi_order_id like '" . esc_sql( $order_id ) . "' " );		
		if ( count( $results ) ) {
			$order = wc_get_order( $order_id );
			foreach ( $results as $row ) { // Only one must be found
				$tracking_code = $row->cdi_tracking;
				$cdi_parcelNumberPartner = $row->cdi_parcelNumberPartner;
				$cdi_parcelNumberPartner = str_replace( ' ', '', $cdi_parcelNumberPartner );
				$url_label = $row->cdi_hreflabel;
				if ( $row->cdi_status == 'open' or null == $row->cdi_status ) {
					$order->update_meta_data( '_cdi_meta_tracking', $tracking_code );
					$order->update_meta_data( '_cdi_meta_parcelNumberPartner', $cdi_parcelNumberPartner );
					$order->update_meta_data( '_cdi_meta_urllabel', $url_label );
					$order->save();
				}
				// Reset to cdi status "waiting" in some cases of cleanning tracking
				$cdi_statusmetabox = $order->get_meta( '_cdi_meta_status' );
				$cdi_tracking = $order->get_meta( '_cdi_meta_tracking' );
				if ( $cdi_statusmetabox == 'intruck' and ( ! $cdi_tracking or $cdi_tracking == '' ) ) {
					$order->update_meta_data( '_cdi_meta_status', 'waiting' );
					$order->save();
				}
			}
		}
	}

	public static function cdi_c_Addgateway_open() {
	}
	public static function cdi_c_Addgateway_add( $orderid ) {
		global $wpdb;
		$order = wc_get_order( $orderid );
		$status = $order->get_meta( '_cdi_meta_status' );
		if ( $status ) { // Check if Metabox CDI exist
			$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . "cdi where cdi_order_id like '" . esc_sql( $orderid ) . "' " );
			if ( ! count( $results ) ) {
				// Check if Shipping necessary
				$ship_to_do = false ;				
				$items_chosen = cdi_c_Function::cdi_get_items_chosen( $order );
				foreach ( $items_chosen as $item ) {
					$product_id = $item['variation_id'];
					if ( $product_id == 0 ) { // No variation for that one
						$product_id = $item['product_id'];
					}
					$product = wc_get_product( $product_id );
					if ($product->is_virtual() !== true ) {
						$ship_to_do = true ;
					}else{
						$message = 'CDI : Order ' . $orderid . ' is not shippable.';
						update_option( 'cdi_o_notice_display', $message );
					}
				}		
				if (!$ship_to_do) { // Case No shipping order (Vitual, ...)
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, sanitize_text_field( 'Order : ' . $orderid . ' is not shippable.'), 'tec' );
					return false ;
				}			
				$tracking_code = $order->get_meta( '_cdi_meta_tracking' );
				$parcelNumberPartner = $order->get_meta( '_cdi_meta_parcelNumberPartner' );
				$url_label = $order->get_meta( '_cdi_meta_urllabel' );
				$wpdb->query( 'insert into ' . $wpdb->prefix . "cdi (cdi_order_id, cdi_tracking) values ('" . esc_sql( $orderid ) . "', '" . '' . "')" );
				$id = $wpdb->insert_id;
				$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $orderid );
				$cdi_status = apply_filters( 'cdi_filterstring_gateway_statusatinit', 'open', $orderid, $array_for_carrier );
				$x = $wpdb->update(
					$wpdb->prefix . 'cdi',
					array(
						'cdi_tracking' => $tracking_code,
						'cdi_parcelNumberPartner' => $parcelNumberPartner,
						'cdi_hreflabel' => $url_label,
						'cdi_status' => $cdi_status,
					),
					array( 'cdi_order_id' => $orderid )
				);
				return true ;
			}
		}
	}
	public static function cdi_c_Addgateway_close() {
	}

}
?>
