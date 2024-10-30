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
/****************************************************************************************/
/* Add Bordereaux CDI actions in the Gateway                                            */
/****************************************************************************************/
class cdi_c_Gateway_Bordereaux {
	public static function init() {
		add_action( 'wp_ajax_cdi_bremise_open_view', __CLASS__ . '::cdi_ajax_bremise_open_view' );
		add_action( 'wp_ajax_cdi_bremise_close_view', __CLASS__ . '::cdi_ajax_bremise_close_view' );
		add_action( 'wp_ajax_cdi_bremise_add_select', __CLASS__ . '::cdi_ajax_bremise_add_select' );
		add_action( 'wp_ajax_cdi_bremise_clear_select', __CLASS__ . '::cdi_ajax_bremise_clear_select' );

		add_action( 'wp_ajax_cdi_bcarrier_exec_bordereau', __CLASS__ . '::cdi_ajax_bcarrier_exec_bordereau' );

		add_action( 'wp_ajax_cdi_btransport_exec_bordereau', __CLASS__ . '::cdi_ajax_btransport_exec_bordereau' );
		add_action( 'wp_ajax_cdi_bpreparation_exec_bordereau', __CLASS__ . '::cdi_ajax_bpreparation_exec_bordereau' );
		add_action( 'wp_ajax_cdi_blivraison_exec_bordereau', __CLASS__ . '::cdi_ajax_blivraison_exec_bordereau' );
		add_action( 'wp_ajax_cdi_bremise_exec_bordereau', __CLASS__ . '::cdi_ajax_bremise_exec_bordereau' );

		add_action( 'wp_ajax_cdi_bbulklabelpdf_exec_bordereau', __CLASS__ . '::cdi_ajax_bbulklabelpdf_exec_bordereau' );
		add_action( 'wp_ajax_cdi_bbulkcn23pdf_exec_bordereau', __CLASS__ . '::cdi_ajax_bbulkcn23pdf_exec_bordereau' );
		add_action( 'wp_ajax_cdi_bhistocsv_exec_bordereau', __CLASS__ . '::cdi_ajax_bhistocsv_exec_bordereau' );

		add_action( 'admin_init', __CLASS__ . '::cdi_bordereau_voir' );
	}

	/**
	 * Manage the Bordereau Remise function.
	 */
	public static function bremise_manage() {
		if ( ! get_option( 'cdi_o_Br_select_carrier' ) ) {
			update_option( 'cdi_o_Br_select_carrier', 'mondialrelay' );
		}
		if ( ! get_option( 'cdi_o_Br_select_fromdate' ) or ! get_option( 'cdi_o_Br_select_todate' ) ) {
			update_option( 'cdi_o_Br_select_todate', date( 'Y-m-d' ) );
			update_option( 'cdi_o_Br_select_fromdate', date( 'Y-m-d', strtotime( '-1 days' ) ) );
		}
		if ( ! get_option( 'cdi_o_Br_select_numorders' ) ) {
			update_option( 'cdi_o_Br_select_numorders', '' );
		}
		if ( ! get_option( 'cdi_o_Br_select_codesuivi' ) ) {
			update_option( 'cdi_o_Br_select_codesuivi', '' );
		}
		if ( ! get_option( 'cdi_o_Br_select_extern' ) ) {
			update_option( 'cdi_o_Br_select_extern', '' );
		}
		if ( ! get_option( 'cdi_o_Br_select_annulcode' ) ) {
			update_option( 'cdi_o_Br_select_annulcode', '' );
		}
		$carrier = get_option( 'cdi_o_Br_select_carrier' );
		?>
		<div id="bremisemanagewrap">
		  <div id="bremisemanage" style="display:none;">
			<h2>CDI Gestion des documents logistiques</h2>
			<p>
			  <input type="button" id="cdi-bremise-close" class="button button-primary mode-run-bremise" value="<?php _e( 'Close', 'cdi' ); ?>" />
			  <input type="button" id="cdi-bremise-select" class="button button-primary" value="<?php _e( 'Add selection (s)', 'cdi' ); ?>" />
			  <input type="button" id="cdi-bremise-clear" class="button button-primary" value="<?php _e( 'Purge selected', 'cdi' ); ?>" />
			  
			  <a style="margin-left:40px;"> </a>
			  <select id="cdi-bcarrier-exec" name="cdi-bcarrier-exec" style="background-color:white;" Title="<?php _e( 'Select the carrier for which the logistics documents (green buttons) will be generated.', 'cdi' ); ?>" >
				<option value="colissimo" <?php echo ( $carrier == 'colissimo' ? 'selected' : '' ); ?> >Colissimo</option>
				<option value="mondialrelay" <?php echo ( $carrier == 'mondialrelay' ? 'selected' : '' ); ?> >Mondial Relay</option>                
				<option value="ups" <?php echo ( $carrier == 'ups' ? 'selected' : '' ); ?> >UPS</option> 
				<option value="collect" <?php echo ( $carrier == 'collect' ? 'selected' : '' ); ?> >Collect</option>
				<option value="notcdi" <?php echo ( $carrier == 'notcdi' ? 'selected' : '' ); ?> > <?php echo cdi_c_Function::cdi_get_libelle_carrier( 'notcdi' ); ?></option>
			  </select>     
			  <input type="button" id="cdi-btransport-exec" class="button button-primary" style="background-color:green;" value="<?php _e( 'Travel voucher', 'cdi' ); ?>" Title="<?php _e( 'Generate a transport voucher. This document is intended for the logistics department, a service provider, or a carrier, who have to deliver the packages. It contains gathered in one sheet only the external characteristics of the package selection, without detailing the content of each package', 'cdi' ); ?>"/>
			  <input type="button" id="cdi-bpreparation-exec" class="button button-primary" style="background-color:green;" value="<?php _e( 'Preparation lists', 'cdi' ); ?>" Title="<?php _e( 'Generate Preparation Lists. These documents are intended for the logistics department or a service provider, who in particular have to prepare the packages. They consist of independent sheets per package giving details of the items to be in each package.', 'cdi' ); ?>"/>
			  <input type="button" id="cdi-blivraison-exec" class="button button-primary" style="background-color:green;" value="<?php _e( 'Status of deliveries', 'cdi' ); ?>" Title="<?php _e( 'Document giving the overall status of deliveries for the selected packages.', 'cdi' ); ?>"/>
			  <input type="button" id="cdi-bremise-exec" class="button button-primary" style="background-color:green;" value="<?php _e( 'Deposit voucher', 'cdi' ); ?>" Title="<?php _e( 'Generate a delivery slip for the carrier. This document is to be given when the parcels are dropped off at carrier. It may be optional according to the deposit terms agreed in the contract with the carrier.', 'cdi' ); ?>"/>
			  <a style="margin-left:40px;"> </a>
			  <input type="button" id="cdi-bbulklabelpdf-exec" class="button button-primary" style="background-color:beige; color: black;" value="<?php _e( 'bulk labels', 'cdi' ); ?>" Title="<?php _e( 'Here you can export a bulk printing of your label parcels / parcels already processed.', 'cdi' ); ?>" />                    
			  <input type="button" id="cdi-bbulkcn23pdf-exec" class="button button-primary" style="background-color:beige; color: black;" value="<?php _e( 'bulk cn23s', 'cdi' ); ?>" Title="<?php _e( 'Here you can export a bulk printing of your cn23 parcels / parcels already processed.', 'cdi' ); ?>" />                    
			  <input type="button" id="cdi-bhistoriquecsv-exec" class="button button-primary" style="background-color:beige; color: black;" value="<?php _e( 'Orders / parcels history', 'cdi' ); ?>" Title="<?php _e( 'Here you can export a csv history of your orders / parcels already processed. For reprocessing from the csv workbook.', 'cdi' ); ?>" />           
			</p>
			<div id="cdi-bremise-message-zone"></div>
			<div style="width:98%; height:calc(100vh - 50px);display:inline-block;">
			  <div id="cdi-bremise-selectors" style="font-size:14px; width:25%; height:100%; display:inline-block; float:left; margin:10px; padding:10px; background-color:white;">
				<h2 style="text-align: center;"><?php _e( 'Selectors', 'cdi' ); ?></h2>
				<div ><strong><?php _e( 'Selection of Orders / Packages:', 'cdi' ); ?></strong></div>
				  <div>
					<p>
					  <input type="checkbox" id="br_select_gateway" name="br_select_gateway" unchecked style="font-size:14px; margin:10px; padding:10px;">
					  <label for="br_select_gateway"> <strong>+</strong> <?php _e( 'Parcels in the Gateway', 'cdi' ); ?></label>
					</p>
					<p>                  
					  <input type="checkbox" id="br_select_orders" name="br_select_orders" unchecked style="font-size:14px; margin:10px; padding:10px;">
					  <label for="br_select_orders"> <strong>+</strong> <?php _e( 'Packages for orders from date to date (included):', 'cdi' ); ?></label>
					</p>
					<p>
					  <label for="fromdate" style="margin-left:15%;">du </label>
					  <input type="text" class="custom_date" id="cdi_o_Br_select_fromdate" name="cdi_o_Br_select_fromdate" style="width:25%" value="<?php echo esc_attr( get_option( 'cdi_o_Br_select_fromdate' ) ); ?>"/>
					  <lablel for="todate">au </label>
					  <input type="text" class="custom_date" id="cdi_o_Br_select_todate" name="cdi_o_Br_select_todate" style="width:25%" value="<?php echo esc_attr( get_option( 'cdi_o_Br_select_todate' ) ); ?>"/>
					</p>
					<p>                  
					  <input type="checkbox" id="br_select_numorders" name="br_select_numorders" unchecked style="font-size:14px; margin:10px; padding:10px;">
					  <label for="br_select_numorders"> <strong>+</strong> <?php _e( 'Order / package Id list (separating commas): ', 'cdi' ); ?> </label>
					  <textarea id="br_select_numorders_param1" style="font-size:14px; margin-left:15%; width: 80%; height:70px;"><?php echo esc_textarea( get_option( 'cdi_o_Br_select_numorders' ) ); ?></textarea>
					</p>
					<p>                  
					  <input type="checkbox" id="br_select_codesuivi" name="br_select_codesuivi" unchecked style="font-size:14px; margin:10px; padding:10px;">
					  <label for="br_select_codesuivi"> <strong>+</strong> <?php _e( 'list of codes followed (separating commas):', 'cdi' ); ?></label>
					  <textarea id="br_select_codesuivi_param1" style="font-size:14px; margin-left:15%; width: 80%; height:70px;"><?php echo esc_textarea( get_option( 'cdi_o_Br_select_codesuivi' ) ); ?></textarea>
					</p>
					<p>                  
					  <input type="checkbox" id="br_select_extern" name="br_select_extern" unchecked style="font-size:14px; margin:10px; padding:10px;">
					  <label for="br_select_extern"> <strong>+</strong> <?php _e( "External (scan, app, etc) (separator commas, 'orderid, [codetracking], ...' syntax) or by a change of the Wordpress option 'cdi_o_Br_select_extern' ", 'cdi' ); ?></label>
					  <textarea id="br_select_extern_param1" style="font-size:14px; margin-left:15%; width: 80%; height:70px;"><?php echo esc_textarea( get_option( 'cdi_o_Br_select_extern' ) ); ?></textarea>
					</p>
					<p>                  
					  <input type="checkbox" id="br_select_annulcode" name="br_select_annulcode" unchecked style="font-size:14px; margin:10px; padding:10px;">
					  <label for="br_select_annulcode"> <strong>-</strong> <?php _e( "Cancellation of Orders / Parcels (separator commas, 'orderid, [codetracking], ...' syntax):", 'cdi' ); ?></label>
					  <textarea id="br_select_annulcode_param1" style="font-size:14px; margin-left:15%; width: 80%; height:70px;"><?php echo esc_textarea( get_option( 'cdi_o_Br_select_annulcode' ) ); ?></textarea>
					</p>
				  </div>
			  </div>
			  <div id="cdi-bremise-selected" style="font-size:14px; width:25%; height:100%; display:inline-block; float:left; margin:10px; padding:10px; background-color:white;">
				<h2 style="text-align: center;"><?php _e( 'Selected (all carriers)', 'cdi' ); ?></h2>
				<div id="cdi-bremise-listeselected" style="margin:5%; height:80%; overflow: scroll;"></div>
			  </div>
			  <div id="cdi-bremise-cree" style="font-size:14px; width:40%; height:100%; display:inline-block; float:left; margin:10px; padding:10px; background-color:white;">
				<h2 style="text-align: center;"><?php _e( 'Your logistics documents', 'cdi' ); ?></h2>
				<div title= "BT = Bon de transport; LP = Listes de préparation; EL = Etat des livraisons; BD = Bordereau de remise (dépots)"><strong><?php _e( 'History of generated documents', 'cdi' ); ?></strong>(BT=Bon de transport; LP=Listes de préparation; EL=Etat des livraisons; BD=Bordereau de remise (dépots); LA=Etiquettes; CN=Cn23; HI=Historique CSV.) :</div>
				  <div id="cdi-brbordereaux-generes" style="margin:2%; height:80%; overflow: scroll;">
					<form method="post">
					  <div id="outer" style="position: relative;">
						<div id="inner" style="overflow: auto; max-height:80%;">
						  <table cellspacing="0" class="wp-list-table widefat fixed">
							<thead>
							  <tr>
								<th class="manage-column" id="cdi-br-date" scope="col" style="width:30%;"><?php _e( 'Creation date', 'cdi' ); ?></th>
								<th class="manage-column" id="cdi-br-number" scope="col" style="width:45%;"><?php _e( 'Type and reference', 'cdi' ); ?></th>
								<th class="manage-column" id="cdi-br-nbcol" scope="col" style="width:10%;"><?php _e( 'Parcel', 'cdi' ); ?></th>
								<th class="manage-column" id="cdi-br-voir" scope="col" style="width:15%;"><?php _e( 'View', 'cdi' ); ?></th>
							  </tr>
							</thead>
							<tbody id="cdi-list-bordereau"></tbody>
							<tfoot>
							  <tr>
								<th class="manage-column" id="cdi-br-date" scope="col" style="width:30%;"><?php _e( 'Creation date', 'cdi' ); ?></th>
								<th class="manage-column" id="cdi-br-number" scope="col" style="width:45%;"><?php _e( 'Type and reference', 'cdi' ); ?></th>
								<th class="manage-column" id="cdi-br-nbcol" scope="col" style="width:10%;"><?php _e( 'Parcel', 'cdi' ); ?></th>
								<th class="manage-column" id="cdi-br-voir" scope="col" style="width:15%;"><?php _e( 'View', 'cdi' ); ?></th>
							  </tr>
							</tfoot>
						  </table>
						</div>
					  </div>
					</form>
				  </div>
			  </div>
			</div>
			<p> </p>
		  <!-- End of div id="bremisemanage" -->
		  </div>
		</div> 
		<?php
	}
	public static function cdi_bremise_open_button() {
		$cdi_o_Bremise_set = get_option( 'cdi_o_Bremise_set' );
		if ( $cdi_o_Bremise_set !== 'yes' ) {
			$color = '#0085ba'; // Bordereau management is close
		} else {
			$color = 'red'; // Bordereau management is running
		}
		?>
		<em></em><input type="button" id="cdi-bremise-open" class="mode-run-bremise" value="<?php _e( 'Logistics documents', 'cdi' ); ?>" style="float: left; background-color: <?php echo esc_attr( $color ); ?>; color:white;" title="<?php _e( 'Management of logistics documents. To open click!', 'cdi' ); ?>" /><em></em><?php
		$ajaxurl = admin_url( 'admin-ajax.php' );
	}
	public static function cdi_get_br_selected() {
		$selected = get_option( 'cdi_o_Selected_bremise' );
		if ( ! $selected ) {
			$selected = array();
			update_option( 'cdi_o_Selected_bremise', $selected );
		}
		return $selected;
	}
	public static function cdi_clean_br_selected() {
		// Clean discarted of orders no more present in WC
		$selected = get_option( 'cdi_o_Selected_bremise' );
		if ( ! $selected ) {
			$selected = array();
		}
		$selectedclean  = array();
		foreach ( $selected as $sel ) {
			$tracking = cdi_c_Function::get_string_between( $sel, '[', ']' );
			$orderid = str_replace( ' [' . $tracking . ']', '', $sel );
			if ( wc_get_order( $orderid ) !== false ) {
				$selectedclean[] = $sel;
			}
		}
		update_option( 'cdi_o_Selected_bremise', $selectedclean );
	}
	public static function cdi_get_br_displayselected( $selected ) {
		$selecteddisplay = '';
		foreach ( $selected as $sel ) {
			$tracking = cdi_c_Function::get_string_between( $sel, '[', ']' );
			$orderid = str_replace( ' [' . $tracking . ']', '', $sel );
			if ( wc_get_order( $orderid ) !== false ) {
				$order = new WC_Order( $orderid );
				$ordernumber = $order->get_order_number();
				$selecteddisplay .= $orderid . '(' . $ordernumber . ') [' . $tracking . ']</br>';
			} else {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'CDI - Logistic documents : this order not more exist : ' . $orderid, 'tec' );
			}
		}
		return $selecteddisplay;
	}
	public static function cdi_ajax_bremise_open_view() {
		update_option( 'cdi_o_Bremise_set', 'yes' );
		$selected = self::cdi_get_br_selected();
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		$bordereauxdisplay = self::cdi_body_table_bordereau();
		echo wp_json_encode(
			array(
				'message' => __( 'Welcome in the manager of your logistics documents', 'cdi' ),
				'htmlselected' => $selecteddisplay,
				'htmlbordereaux' => $bordereauxdisplay,
			)
		);
		wp_die();
	}
	public static function cdi_ajax_bremise_close_view() {
		delete_option( 'cdi_o_Br_select_todate' );
		delete_option( 'cdi_o_Br_select_fromdate' );
		delete_option( 'cdi_o_Br_select_numorders' );
		delete_option( 'cdi_o_Br_select_codesuivi' );
		delete_option( 'cdi_o_Br_select_extern' );
		delete_option( 'cdi_o_Br_select_annulcode' );
		delete_option( 'cdi_o_Bremise_set' );
		wp_die();
	}
	public static function cdi_add_sel( $selected, $orderid, $codesuivi ) {
		$returnselected = $selected;
		if ( $orderid ) {
			if ( wc_get_order( $orderid ) !== false ) {
				$sel = $orderid;
				if ( $codesuivi ) {
					$sel = $sel . ' [' . strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $codesuivi ) ) . ']';
				}
			}
			$returnselected[] = $sel;
		}
		return $returnselected;
	}
	public static function cdi_ajax_bremise_add_select() {
		global $wpdb;
		global $woocommerce;
		global $message;
		$select = cdi_c_Function::cdi_sanitize_array( $_POST['select'] );
		update_option( 'cdi_o_Br_select_todate', $select['cdi_o_Br_select_todate'] );
		update_option( 'cdi_o_Br_select_fromdate', $select['cdi_o_Br_select_fromdate'] );
		update_option( 'cdi_o_Br_select_numorders', sanitize_text_field( $select['br_select_numorders_param1'] ) );
		update_option( 'cdi_o_Br_select_codesuivi', sanitize_text_field( $select['br_select_codesuivi_param1'] ) );
		update_option( 'cdi_o_Br_select_extern', sanitize_text_field( $select['br_select_extern_param1'] ) );
		update_option( 'cdi_o_Br_select_annulcode', sanitize_text_field( $select['br_select_annulcode_param1'] ) );
		$selected = self::cdi_get_br_selected();
		if ( $select['br_select_gateway'] == 'true' ) {
			$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'cdi' );
			if ( count( $results ) > 0 ) {
				foreach ( $results as $row ) {
					if ( $row->cdi_tracking ) {
						if ( ( $row->cdi_status == 'open' or $row->cdi_status == null ) and wc_get_order( $row->cdi_order_id ) !== false ) {
							$selected = self::cdi_add_sel( $selected, $row->cdi_order_id, $row->cdi_tracking );
						}
					}
				}
			}
		}
		if ( $select['br_select_orders'] == 'true' and $select['cdi_o_Br_select_fromdate'] !== '' and $select['cdi_o_Br_select_todate'] !== '' ) {
			$datetime1 = new DateTime( str_replace( '-', '', $select['cdi_o_Br_select_fromdate'] ) );
			$datetime2 = new DateTime( str_replace( '-', '', $select['cdi_o_Br_select_todate'] ) );
			$difference = $datetime1->diff( $datetime2 );
			//
			// **** Limit of search = 365 days and a max of 500 orders
			//
			if ( ( $difference->invert == 0 ) && ( $difference->days < 1500 ) ) {
				$selecteddates = date( 'Y-m-d', strtotime( $select['cdi_o_Br_select_fromdate'] . ' -1 day' ) ) . '...' . date( 'Y-m-d', strtotime( $select['cdi_o_Br_select_todate'] . ' +1 day' ) ) ;			
				$customer_orders = wc_get_orders( array(
						'limit' => 500,
						'orderby'     => 'date',
						'order'       => 'DESC',
						'date_created' => $selecteddates ,
						),
				);
				foreach ( $customer_orders as $customer_order ) {
					  $order = wc_get_order( $customer_order );
					  $orderid = $order->get_id();
					  $cdi_tracking = $order->get_meta( '_cdi_meta_tracking' );
					if ( $cdi_tracking ) {
						$selected = self::cdi_add_sel( $selected, $orderid, $cdi_tracking );
					}
				}
			}
		}
		if ( $select['br_select_numorders'] == 'true' and $select['br_select_numorders_param1'] !== '' ) {
			$arraylistenumorders = explode( ',', $select['br_select_numorders_param1'] );
			foreach ( $arraylistenumorders as $orderid ) {
				$orderid = preg_replace( '/[^0-9]/', '', $orderid );
				$order = wc_get_order($orderid) ;				
				if ( $order !== false ) {
					$cdi_tracking = $order->get_meta( '_cdi_meta_tracking' );
					if ( $cdi_tracking ) {
						$selected = self::cdi_add_sel( $selected, $orderid, $cdi_tracking );
					}
				}
			}
		}
		if ( $select['br_select_codesuivi'] == 'true' and $select['br_select_codesuivi_param1'] !== '' ) {
			$arraylistecodesuivi = explode( ',', $select['br_select_codesuivi_param1'] );
			foreach ( $arraylistecodesuivi as $codesuivi ) {
				$codesuivi = trim($codesuivi) ;						
				$codesuivi = preg_replace( '/[^A-Z0-9]/', '', $codesuivi );
				if ( $codesuivi ) {
					$args = array(
						'meta_key'      => '_cdi_meta_tracking',
						'meta_value'    => $codesuivi, 
						'meta_compare'  => '=', 
						'return'        => 'objects',
						'limit' 	=> 1,
						);
					$results = wc_get_orders( $args );
					if ( count( $results ) > 0 ) {
						$orderid = $results[0]->get_id();
						$selected = self::cdi_add_sel( $selected, $orderid, $codesuivi );
					}
				}
			}
		}
		if ( $select['br_select_extern'] == 'true' and $select['br_select_extern_param1'] !== '' ) {
			$arraylisteextern = explode( ',', $select['br_select_extern_param1'] );
			foreach ( $arraylisteextern as $extern ) {
				$extern = trim($extern) ;
				if ( is_numeric($extern) ) { // it is an orderid
					$order = wc_get_order( $extern ) ;
					if ( $order !== false ) {
						$cdi_tracking = $order->get_meta( '_cdi_meta_tracking' );
						$selected = self::cdi_add_sel( $selected, $extern, $cdi_tracking );
					}
				} else { // it is a tracking code
					$code = cdi_c_Function::get_string_between( $extern, '[', ']' );
					$code = trim($code) ;
					if (! $code) {
					   continue ;
					}
					$args = array(
						'meta_key'      => '_cdi_meta_tracking',
						'meta_value'    => $code, 
						'meta_compare'  => '=', 
						'return'        => 'objects',
						'limit' 	=> 1,
						);
					$results = wc_get_orders( $args );
					if ( count( $results ) > 0 ) {
						$orderid = $results[0]->get_id() ;
						$selected = self::cdi_add_sel( $selected, $orderid, $extern );
					}
				}
			}
		}
		if ( $select['br_select_annulcode'] == 'true' and $select['br_select_annulcode_param1'] !== '' ) {
			$arraylisteannulcode = explode( ',', $select['br_select_annulcode_param1'] );
			foreach ( $arraylisteannulcode as $annulcode ) {
				$annulcode = trim($annulcode) ;		
				if ( is_numeric($annulcode) ) {				
					$annulcode = preg_replace( '/[^A-Z0-9]/', '', $annulcode );					
					$annulcode = trim($annulcode) ;
					$order = wc_get_order( $annulcode) ;
					if ( $order !== false ) {
						$cdi_tracking = $order->get_meta( '_cdi_meta_tracking' );
						$tosupress = $annulcode . ' [' . strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $cdi_tracking ) ) . ']';
						$selected = array_diff( $selected, array( $tosupress ) );
					}
				} else { // it is a tracking code
					$annulcode = trim($annulcode) ;
					$tracking = cdi_c_Function::get_string_between( $annulcode, '[', ']' );
					$args = array(
						'meta_key'      => '_cdi_meta_tracking',
						'meta_value'    => $tracking, 
						'meta_compare'  => '=', 
						'return'        => 'objects',
						'limit' 	=> 1,
						);
					$results = wc_get_orders( $args );
					if ( count( $results ) > 0 ) {
						$orderid = $results[0]->get_id() ;
						$order = $results[0] ; 					
						$cdi_tracking = $order->get_meta( '_cdi_meta_tracking' );
						$tosupress = $orderid . ' [' . strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $cdi_tracking ) ) . ']';
						$selected = array_diff( $selected, array( $tosupress ) );
					}
				}
			}
		}
		$maxitemlogistic = get_option( 'cdi_o_settings_maxitemlogistic' );
		if ( ! $maxitemlogistic ) {
			$maxitemlogistic = 100;
		}
		$selected = array_unique( $selected );
		rsort( $selected, SORT_NUMERIC );
		if ( count( $selected ) > $maxitemlogistic ) {
			$array = array();
			foreach ( $selected as $sel ) {
				if ( count( $array ) < $maxitemlogistic ) {
					$array[] = $sel;
				}
			}
			$selected = $array;
			update_option( 'cdi_o_Selected_bremise', $selected );
			$selecteddisplay = self::cdi_get_br_displayselected( $selected );
			echo wp_json_encode(
				array(
					'message' => count( $selected ) . ' commandes/colis sélectionnés. <mark>Dépassement de la limite du nombre de colis. Uniquement les commandes les plus récentes sont retenues pour la confection de vos documents.</mark>',
					'htmlselected' => $selecteddisplay,
				)
			);
			wp_die();
		}
		update_option( 'cdi_o_Selected_bremise', $selected );
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		echo wp_json_encode(
			array(
				'message' => count( $selected ) . ' commandes/colis sélectionnés, qui sont en attente pour la confection de vos documents.',
				'htmlselected' => $selecteddisplay,
			)
		);
		wp_die();
	}
	public static function cdi_ajax_bremise_clear_select() {
		$selected = array();
		update_option( 'cdi_o_Selected_bremise', $selected ); // Instead of delete_option initially because not supported by Redis cache
		echo wp_json_encode(
			array(
				'message' => 'La liste des colis sélectionnés a été purgée. Vous pouvez maintenant refaire vos sélections.',
				'htmlselected' => '',
			)
		);
		wp_die();
	}

	public static function cdi_ajax_bcarrier_exec_bordereau() {
		$carrier = sanitize_text_field( $_POST['carrier'] );
		update_option( 'cdi_o_Br_select_carrier', $carrier );
		$selected = self::cdi_get_br_selected();
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		$message = __( 'Your logistics documents will now be produced for your orders with the carrier : ', 'cdi' ) . ' <strong> ' . cdi_c_Function::cdi_get_libelle_carrier( $carrier ) . '</strong> . ' . __( 'The carrier selection will be made during the production of each document.', 'cdi' );
		echo wp_json_encode(
			array(
				'message' => $message,
				'htmlselected' => $selecteddisplay,
			)
		);
		wp_die();
	}

	public static function cdi_ajax_bremise_exec_bordereau() {
		self::cdi_clean_br_selected();
		global $base64bordereau;
		$carrier = get_option( 'cdi_o_Br_select_carrier' );
		$carrier = cdi_c_Function::cdi_fallback_carrier( $carrier );
		$selected = self::cdi_get_br_selected();
		$selected = self::cdi_filtre_carrier_selected( $selected );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_prod_remise_bordereau';
		$message = ( $route )( $selected );
		cdi_c_Function::cdi_stat( 'BOR-col' );
		$selected = self::cdi_get_br_selected();
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		$bordereauxdisplay = self::cdi_body_table_bordereau();
		echo wp_json_encode(
			array(
				'message' => $message,
				'htmlselected' => $selecteddisplay,
				'htmlbordereaux' => $bordereauxdisplay,
			)
		);
		wp_die();
	}

	public static function cdi_ajax_btransport_exec_bordereau() {
		self::cdi_clean_br_selected();
		$errorws = '';
		$carrier = get_option( 'cdi_o_Br_select_carrier' );
		$selected = self::cdi_get_br_selected();
		$selected = self::cdi_filtre_carrier_selected( $selected );
		if ( count( $selected ) > 0 ) {
			cdi_c_Function::cdi_stat( 'BOR-btr' );
			$nbcolis = count( $selected );
			$refbt = date( 'YmdHis' );
			$datetimebt = date( 'Y-m-d H:i:s' );
			$pdf = new FPDI();
			$pdf->AliasNbPages();
			$pdf->AddPage( 'L', 'A4' ); // Paysage
			$pdf->SetAutoPageBreak( 'on', 10 );
			$pdf->SetTextColor( 0, 0, 0 );
			// $pdf->SetDrawColor(0,0,0) ;
			$pdf->SetFillColor( 255, 255, 255 );
			// Title
			$pdf->SetFont( 'Arial', 'BU', 16 );
			$pdf->Cell( 100 );
			$pdf->Cell( 0, 20, 'BON DE TRANSPORT', 0, 1 );
			$pdf->SetFont( 'Arial', 'B', 12 );
			$pdf->Text( 125, 27, utf8_decode( cdi_c_Function::cdi_get_libelle_carrier( $carrier ) ) );
			// Bordereau refs
			$pdf->SetFont( 'Arial', 'B', 12 );
			$x = 215;
			$y = 30;
			$pdf->Text( $x, $y + 0, utf8_decode( 'Créé le : ' . $datetimebt ) );
			$pdf->Text( $x, $y + 5, utf8_decode( 'BT Référence : BT-' . $refbt ) );
			$pdf->Text( $x, $y + 10, utf8_decode( 'Nombre colis : ' . $nbcolis ) );
			// Dest
			$pdf->SetFont( 'Arial', 'B', 12 );
			$x = 110;
			$y = 40;
			$pdf->Text( $x, $y + 0, utf8_decode( '................................................' ) );
			// Merchand
			$pdf->SetFont( 'Arial', 'B', 12 );
			$x = 15;
			$y = 30;
			$pdf->Text( $x, $y + 0, utf8_decode( get_option( 'cdi_o_settings_merchant_CompanyName' ) ) );
			$pdf->Text( $x, $y + 5, utf8_decode( get_option( 'cdi_o_settings_merchant_Line1' ) ) );
			$pdf->Text( $x, $y + 10, utf8_decode( get_option( 'cdi_o_settings_merchant_Line2' ) ) );
			$pdf->Text( $x, $y + 15, utf8_decode( get_option( 'cdi_o_settings_merchant_ZipCode' ) . ' ' . get_option( 'cdi_o_settings_merchant_City' ) ) );
			// En-tete
			$pdf->SetFont( 'Arial', 'B', 12 );
			$pdf->SetXY( 10, 50 );
			$pdf->Cell( 0, 1, '', 1, 1 ); // Line
			$pdf->Cell( 20, 10, '', 0, 0, 'C' ); // Rang
			$pdf->Cell( 30, 10, 'Id', 0, 0, 'C' );
			$pdf->Cell( 30, 10, 'Suivi', 0, 0, 'C' );
			$pdf->Cell( 80, 10, 'Adresse(L1)', 0, 0, 'C' );
			$pdf->Cell( 10, 10, 'CP', 0, 0, 'C' );
			$pdf->Cell( 50, 10, 'Ville', 0, 0, 'C' );
			$pdf->Cell( 10, 10, 'Pays', 0, 0, 'C' );
			$pdf->Cell( 15, 10, 'kg', 0, 0, 'C' );
			$pdf->Cell( 15, 10, 'dm3', 0, 0, 'C' );
			$pdf->Cell( 10, 10, 'NS', 0, 0, 'C' );
			$pdf->Cell( 0, 10, '', 0, 1 ); // End
			$pdf->Cell( 0, 1, '', 1, 1 ); // Line
			// Parcels
			$pdf->SetFont( 'Arial', '', 10 );
			$rang = 0;
			$totalweight = 0;
			$totalvolume = 0;
			foreach ( $selected as $parcel ) {
				$rang = $rang + 1;
				$tracking = cdi_c_Function::get_string_between( $parcel, '[', ']' );
				$orderid = str_replace( ' [' . $tracking . ']', '', $parcel );
				$order = new WC_Order( $orderid );
				$ordernumber = $order->get_order_number();
				$volorder = 0;
				$items_chosen = cdi_c_Function::cdi_get_items_chosen( $order );
				foreach ( $items_chosen as $item ) {
					$product_id = $item['variation_id'];
					if ( $product_id == 0 ) { // No variation for that one
						$product_id = $item['product_id'];
					}
					$product = wc_get_product( $product_id );
					// $prodname = $product->get_name();
					// $sku = $product->get_sku();
					$quantity = $item['qty'];
					$dimensions = $product->get_dimensions( false );
					if ( $dimensions['length'] && $dimensions['width'] && $dimensions['height'] ) {
						$vol = $dimensions['length'] * $dimensions['width'] * $dimensions['height'];
						if ( get_option( 'woocommerce_dimension_unit' ) == 'cm' ) {
							$vol = $vol / 1000; // Convert cm3 to dm3
						} else {
							$vol = $vol * 1000; // Convert m3 to dm3
						}
					} else {
						$vol = 0;
					}
					$vol = $vol * $quantity;
					$volorder = $volorder + $vol;
					$totalvolume = $totalvolume + $vol;
				}
				$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $orderid );
				$pdf->Cell( 20, 10, $rang, 0, 0, 'C' );
				$pdf->Cell( 30, 10, $orderid . '(' . $ordernumber . ')', 0, 0, 'C' );
				$pdf->Cell( 30, 10, $tracking, 0, 0, 'C' );
				$pdf->Cell( 80, 10, $array_for_carrier['shipping_address_1'], 0, 0, 'C' );
				$pdf->Cell( 10, 10, $array_for_carrier['shipping_postcode'], 0, 0, 'C' );
				$pdf->Cell( 50, 10, $array_for_carrier['shipping_city_state'], 0, 0, 'C' );
				$pdf->Cell( 10, 10, $array_for_carrier['shipping_country'], 0, 0, 'C' );
				$totalweight = $totalweight + ( (float)($array_for_carrier['parcel_weight']) / 1000 );
				$pdf->Cell( 15, 10, (float)($array_for_carrier['parcel_weight']) / 1000, 0, 0, 'C' );
				$pdf->Cell( 15, 10, $volorder, 0, 0, 'C' );
				$NonMachinable = str_replace( array( 'colis-standard', 'colis-volumineux', 'colis-rouleau' ), array( '', 'volu', 'roul' ), $array_for_carrier['parcel_type'] );
				$pdf->Cell( 10, 10, $NonMachinable, 0, 0, 'C' );
				$pdf->Cell( 0, 10, '', 0, 1 ); // End
			}
			$pdf->Cell( 0, 1, '', 1, 1 ); // Line
			// Sum
			// $pdf->Cell(0,5,' ',0,1); // Blank line
			$pdf->Cell( 30, 10, 'TOTAUX : ', 0, 0, 'C' );
			$pdf->Cell( 200, 10, ' ', 0, 0, 'C' );
			$pdf->Cell( 15, 10, $totalweight, 0, 0, 'C' );
			$pdf->Cell( 15, 10, $totalvolume, 0, 0, 'C' );
			$pdf->Cell( 0, 10, '', 0, 1 ); // End
			// Complete the form
			$pdf->Cell( 0, 5, ' ', 0, 1 ); // Blank line
			$pdf->Cell( 40, 15, 'Visa ................................................', 0, 0, 'L' );
			$pdf->Cell( 0, 15, '', 0, 1 ); // End
			// End write
			$resultpdf = $pdf->Output( 'S' );
			$resultpdf = base64_encode( $resultpdf );		
			self::cdi_stockage_bordereau( 'BT-' . $carrier, $refbt, '=> ' . $datetimebt, $nbcolis, $resultpdf ); // '=> ' only to sure to be the first in sort
		}
		if ( $errorws !== '' ) {
			$message = $errorws;
		} else {
			$message = "Votre bordereau a été généré. C'est le premier de votre liste. Cliquez sur \"Voir\" pour le visualiser ou l'imprimer";
		}
		$selected = self::cdi_get_br_selected();
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		$bordereauxdisplay = self::cdi_body_table_bordereau();
		echo wp_json_encode(
			array(
				'message' => $message,
				'htmlselected' => $selecteddisplay,
				'htmlbordereaux' => $bordereauxdisplay,
			)
		);
		wp_die();
	}

	public static function cdi_ajax_bpreparation_exec_bordereau() {
		self::cdi_clean_br_selected();
		$errorws = '';
		$carrier = get_option( 'cdi_o_Br_select_carrier' );
		$selected = self::cdi_get_br_selected();
		$selected = self::cdi_filtre_carrier_selected( $selected );
		if ( count( $selected ) > 0 ) {
			cdi_c_Function::cdi_stat( 'BOR-pre' );
			$nbcolis = count( $selected );
			$ranglp = 0;
			$reflp = date( 'YmdHis' );
			$datetimelp = date( 'Y-m-d H:i:s' );
			$pdf = new FPDI();
			$pdf->AliasNbPages();
			$pdf->AddPage( 'L', 'A4' ); // Landscape
			$pdf->SetAutoPageBreak( 'on', 10 );
			$pdf->SetTextColor( 0, 0, 0 );
			$pdf->SetFillColor( 255, 255, 255 );
			foreach ( $selected as $parcel ) {
				if ( $ranglp !== 0 ) {
					$pdf->AddPage( 'L', 'A4' ); // Landscape
				}
				$ranglp = $ranglp + 1;
				// Title
				$pdf->SetFont( 'Arial', 'BU', 16 );
				$pdf->Cell( 100 );
				$pdf->Cell( 0, 20, 'LISTE DE PREPARATION', 0, 1 );
				$pdf->SetFont( 'Arial', 'B', 12 );
				$pdf->Text( 125, 27, utf8_decode( cdi_c_Function::cdi_get_libelle_carrier( $carrier ) ) );
				// Liste refs
				$pdf->SetFont( 'Arial', 'B', 12 );
				$x = 215;
				$y = 30;
				$pdf->Text( $x, $y + 0, utf8_decode( 'Créé le : ' . $datetimelp ) );
				$pdf->Text( $x, $y + 5, utf8_decode( 'LP Référence : LP-' . $reflp . '-' . $ranglp ) );
				$pdf->Text( $x, $y + 10, utf8_decode( 'Nombre colis : ' . '1' ) );
				// Dest
				$pdf->SetFont( 'Arial', 'B', 12 );
				$x = 110;
				$y = 40;
				$pdf->Text( $x, $y + 0, utf8_decode( '........................................................' ) );
				// Merchand
				$pdf->SetFont( 'Arial', 'B', 12 );
				$x = 15;
				$y = 30;
				$pdf->Text( $x, $y + 0, utf8_decode( get_option( 'cdi_o_settings_merchant_CompanyName' ) ) );
				$pdf->Text( $x, $y + 5, utf8_decode( get_option( 'cdi_o_settings_merchant_Line1' ) ) );
				$pdf->Text( $x, $y + 10, utf8_decode( get_option( 'cdi_o_settings_merchant_Line2' ) ) );
				$pdf->Text( $x, $y + 15, utf8_decode( get_option( 'cdi_o_settings_merchant_ZipCode' ) . ' ' . get_option( 'cdi_o_settings_merchant_City' ) ) );
				// Customer
				$tracking = cdi_c_Function::get_string_between( $parcel, '[', ']' );
				$orderid = str_replace( ' [' . $tracking . ']', '', $parcel );
				$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $orderid );
				$order = new WC_Order( $orderid );
				$ordernumber = $order->get_order_number();
				$pdf->SetFont( 'Arial', 'B', 12 );
				$pdf->SetXY( 10, 50 );
				$pdf->Cell( 0, 1, '', 1, 1 ); // Line
				$pdf->Cell( 40, 10, 'Destinataire: ', 0, 0, 'L' );
				$pdf->Cell( 0, 10, $array_for_carrier['shipping_company'] . ' ' . $array_for_carrier['shipping_first_name'] . ' ' . $array_for_carrier['shipping_last_name'], 0, 0, 'L' );
				$pdf->Cell( 0, 10, '', 0, 1 ); // End
				$pdf->Cell( 40, 10, '', 0, 0, 'L' );
				$pdf->Cell( 0, 10, $array_for_carrier['shipping_address_1'] . ' ' . $array_for_carrier['shipping_address_2'] . ' ' . $array_for_carrier['shipping_address_3'] . ' ' . $array_for_carrier['shipping_address_4'], 0, 0, 'L' );
				$pdf->Cell( 0, 10, '', 0, 1 ); // End
				$pdf->Cell( 40, 10, '', 0, 0, 'L' );
				$pdf->Cell( 0, 10, $array_for_carrier['shipping_postcode'] . ' ' . $array_for_carrier['shipping_city_state'] . ' ' . $array_for_carrier['shipping_country'], 0, 0, 'L' );
				$pdf->Cell( 0, 10, '', 0, 1 ); // End
				$pdf->Cell( 40, 10, '', 0, 0, 'L' );
				$pdf->Cell( 0, 10, $array_for_carrier['billing_phone'] . ' / ' . $array_for_carrier['billing_email'], 0, 0, 'L' );
				$pdf->Cell( 0, 10, '', 0, 1 ); // End
				// Parcel
				$pdf->Cell( 0, 1, '', 1, 1 ); // Line
				$volorder = 0;
				$items_chosen = cdi_c_Function::cdi_get_items_chosen( $order );
				foreach ( $items_chosen as $item ) {
					$product_id = $item['variation_id'];
					if ( $product_id == 0 ) { // No variation for that one
						$product_id = $item['product_id'];
					}
					$product = wc_get_product( $product_id );
					// $prodname = $product->get_name();
					// $sku = $product->get_sku();
					$quantity = $item['qty'];
					$dimensions = $product->get_dimensions( false );
					if ( $dimensions['length'] && $dimensions['width'] && $dimensions['height'] ) {
						$vol = $dimensions['length'] * $dimensions['width'] * $dimensions['height'];
						if ( get_option( 'woocommerce_dimension_unit' ) == 'cm' ) {
							$vol = $vol / 1000; // Convert cm3 to dm3
						} else {
							$vol = $vol * 1000; // Convert m3 to dm3
						}
					} else {
						$vol = 0;
					}
					$vol = $vol * $quantity;
					$volorder = $volorder + $vol;
				}
				$pdf->Cell( 40, 10, 'Colis: ', 0, 0, 'L' );
				$pdf->Cell( 0, 10, 'Id ' . $orderid . '(' . $ordernumber . ') ; Suivi ' . $tracking . '; Poids total ' . (float)($array_for_carrier['parcel_weight']) / 1000 . 'kg; Volume ' . $volorder . 'dm3; Emballage ' . get_option( 'cdi_o_settings_parceltareweight' ) / 1000 . 'kg' . str_replace( array( 'colis-standard', 'colis-volumineux', 'colis-rouleau' ), array( '; Standard', '; Volumineux', '; Tube' ), $array_for_carrier['parcel_type'] ), 0, 0, 'L' );
				$pdf->Cell( 0, 10, '', 0, 1 ); // End
				// Products
				$pdf->Cell( 0, 1, '', 1, 1 ); // Line
				$pdf->Cell( 40, 10, utf8_decode( 'Articles: ' ), 0, 0, 'L' );
				$pdf->Cell( 40, 10, utf8_decode( 'Référence' ), 0, 0, 'L' );
				$pdf->Cell( 80, 10, utf8_decode( 'Désignation' ), 0, 0, 'L' );
				$pdf->Cell( 20, 10, utf8_decode( 'Quantité' ), 0, 0, 'L' );
				$pdf->Cell( 40, 10, utf8_decode( 'Poids unitaire(kg)' ), 0, 0, 'L' );
				$pdf->Cell( 40, 10, utf8_decode( 'Volume unitaire(dm3)' ), 0, 0, 'L' );
				$pdf->Cell( 0, 10, '', 0, 1 ); // End
				foreach ( $items_chosen as $item ) {
					$product_id = $item['variation_id'];
					if ( $product_id == 0 ) { // No variation for that one
						$product_id = $item['product_id'];
					}
					$product = wc_get_product( $product_id );
					$weight = $product->get_weight();
					if ( $weight and is_numeric( $weight ) and $weight !== 0 ) { // Supp Virtual and no-weighted products
						$prodname = $product->get_name();
						$ugs = $product->get_sku();
						$quantity = $item['qty'];
						if ( get_option( 'woocommerce_weight_unit' ) == 'g' ) {
							$weight = $weight / 1000; // Convert g to kg
						}
						$dimensions = $product->get_dimensions( false );
						if ( $dimensions['length'] && $dimensions['width'] && $dimensions['height'] ) {
							$vol = $dimensions['length'] * $dimensions['width'] * $dimensions['height'];
							if ( get_option( 'woocommerce_dimension_unit' ) == 'cm' ) {
								$vol = $vol / 1000; // Convert cm3 to dm3
							} else {
								$vol = $vol * 1000; // Convert m3 to dm3
							}
						} else {
							$vol = 0;
						}
						$pdf->Cell( 40, 10, '', 0, 0, 'L' );
						$pdf->Cell( 40, 10, utf8_decode( $ugs ), 0, 0, 'L' );
						$pdf->Cell( 80, 10, utf8_decode( $prodname ), 0, 0, 'L' );
						$pdf->Cell( 20, 10, utf8_decode( $quantity ), 0, 0, 'L' );
						$pdf->Cell( 40, 10, utf8_decode( $weight ), 0, 0, 'L' );
						$pdf->Cell( 40, 10, utf8_decode( $vol ), 0, 0, 'L' );
						$pdf->Cell( 0, 10, '', 0, 1 ); // End
					}
				}
				// Complete the form
				$pdf->Cell( 0, 1, '', 1, 1 ); // Line
				$pdf->Cell( 0, 5, ' ', 0, 1 ); // Blank line
				$pdf->Cell( 40, 15, 'Visa : ................................................', 0, 0, 'L' );
				$pdf->Cell( 0, 15, '', 0, 1 ); // End
			}
			// End write
			$resultpdf = $pdf->Output( 'S' );
			$resultpdf = base64_encode( $resultpdf );
			self::cdi_stockage_bordereau( 'LP-' . $carrier, $reflp, '=> ' . $datetimelp, $nbcolis, $resultpdf ); // '=> ' only to sure to be the first in sort
		}
		if ( $errorws !== '' ) {
			$message = $errorws;
		} else {
			$message = "Votre bordereau a été généré. C'est le premier de votre liste. Cliquez sur \"Voir\" pour le visualiser ou l'imprimer";
		}
		$selected = self::cdi_get_br_selected();
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		$bordereauxdisplay = self::cdi_body_table_bordereau();
		echo wp_json_encode(
			array(
				'message' => $message,
				'htmlselected' => $selecteddisplay,
				'htmlbordereaux' => $bordereauxdisplay,
			)
		);
		wp_die();
	}

	public static function cdi_ajax_blivraison_exec_bordereau() {
		self::cdi_clean_br_selected();
		$errorws = '';
		$carrier = get_option( 'cdi_o_Br_select_carrier' );
		$selected = self::cdi_get_br_selected();
		$selected = self::cdi_filtre_carrier_selected( $selected );
		if ( count( $selected ) > 0 ) {
			cdi_c_Function::cdi_stat( 'BOR-liv' );
			$nbcolis = count( $selected );
			$refbt = date( 'YmdHis' );
			$datetimebt = date( 'Y-m-d H:i:s' );
			$pdf = new FPDI();
			$pdf->AliasNbPages();
			$pdf->AddPage( 'L', 'A4' ); // Paysage
			$pdf->SetAutoPageBreak( 'on', 10 );
			$pdf->SetTextColor( 0, 0, 0 );
			// $pdf->SetDrawColor(0,0,0) ;
			$pdf->SetFillColor( 255, 255, 255 );
			// Title
			$pdf->SetFont( 'Arial', 'BU', 16 );
			$pdf->Cell( 100 );
			$pdf->Cell( 0, 20, 'ETAT DES LIVRAISONS', 0, 1 );
			$pdf->SetFont( 'Arial', 'B', 12 );
			$pdf->Text( 125, 27, utf8_decode( cdi_c_Function::cdi_get_libelle_carrier( $carrier ) ) );
			// Bordereau refs
			$pdf->SetFont( 'Arial', 'B', 12 );
			$x = 215;
			$y = 30;
			$pdf->Text( $x, $y + 0, utf8_decode( 'Créé le : ' . $datetimebt ) );
			$pdf->Text( $x, $y + 5, utf8_decode( 'EL Référence : EL-' . $refbt ) );
			$pdf->Text( $x, $y + 10, utf8_decode( 'Nombre colis : ' . $nbcolis ) );
			// Dest
			// $pdf->SetFont('Arial','B',12);
			// $x=110; $y=40;
			// $pdf->Text($x,$y+0,utf8_decode ('................................................'));
			// Merchand
			$pdf->SetFont( 'Arial', 'B', 12 );
			$x = 15;
			$y = 30;
			$pdf->Text( $x, $y + 0, utf8_decode( get_option( 'cdi_o_settings_merchant_CompanyName' ) ) );
			$pdf->Text( $x, $y + 5, utf8_decode( get_option( 'cdi_o_settings_merchant_Line1' ) ) );
			$pdf->Text( $x, $y + 10, utf8_decode( get_option( 'cdi_o_settings_merchant_Line2' ) ) );
			$pdf->Text( $x, $y + 15, utf8_decode( get_option( 'cdi_o_settings_merchant_ZipCode' ) . ' ' . get_option( 'cdi_o_settings_merchant_City' ) ) );
			// En-tete
			$pdf->SetFont( 'Arial', 'B', 12 );
			$pdf->SetXY( 10, 50 );
			$pdf->Cell( 0, 1, '', 1, 1 ); // Line
			$pdf->Cell( 20, 10, '', 0, 0, 'C' ); // Rang
			$pdf->Cell( 30, 10, 'Id', 0, 0, 'C' );
			$pdf->Cell( 30, 10, 'Suivi', 0, 0, 'C' );
			$pdf->Cell( 80, 10, 'Adresse(L1)', 0, 0, 'C' );
			$pdf->Cell( 10, 10, 'CP', 0, 0, 'C' );
			$pdf->Cell( 50, 10, 'Ville', 0, 0, 'C' );
			$pdf->Cell( 10, 10, 'Pays', 0, 0, 'C' );
			$pdf->Cell( 45, 10, 'Livraison Colis', 0, 0, 'C' );
			$pdf->Cell( 0, 10, '', 0, 1 ); // End
			$pdf->Cell( 0, 1, '', 1, 1 ); // Line
			// Parcels
			$pdf->SetFont( 'Arial', '', 10 );
			$rang = 0;
			foreach ( $selected as $parcel ) {
				$rang = $rang + 1;
				$tracking = cdi_c_Function::get_string_between( $parcel, '[', ']' );
				$orderid = str_replace( ' [' . $tracking . ']', '', $parcel );
				$order = new WC_Order( $orderid );
				$ordernumber = $order->get_order_number();
				$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $orderid );
				$pdf->Cell( 0, 5, '', 0, 1 );
				$pdf->Cell( 20, 5, $rang, 0, 0, 'C' );
				$pdf->Cell( 30, 5, $orderid . '(' . $ordernumber . ')', 0, 0, 'C' );
				$pdf->Cell( 30, 5, $tracking, 0, 0, 'C' );
				$pdf->Cell( 80, 5, $array_for_carrier['shipping_address_1'], 0, 0, 'C' );
				$pdf->Cell( 10, 5, $array_for_carrier['shipping_postcode'], 0, 0, 'C' );
				$pdf->Cell( 50, 5, $array_for_carrier['shipping_city_state'], 0, 0, 'C' );
				$pdf->Cell( 10, 5, $array_for_carrier['shipping_country'], 0, 0, 'C' );
				$etat = cdi_c_Function::cdi_get_whereis_parcel( $orderid, $tracking );
				$pdf->MultiCell( 48, 5, $etat );
			}
			$pdf->Cell( 0, 1, '', 1, 1 ); // Line
			$pdf->Cell( 0, 10, '', 0, 1 ); // End
			// End write
			$resultpdf = $pdf->Output( 'S' );
			$resultpdf = base64_encode( $resultpdf );
			self::cdi_stockage_bordereau( 'EL-' . $carrier, $refbt, '=> ' . $datetimebt, $nbcolis, $resultpdf ); // '=> ' only to sure to be the first in sort
		}
		if ( $errorws !== '' ) {
			$message = $errorws;
		} else {
			$message = "Votre bordereau a été généré. C'est le premier de votre liste. Cliquez sur \"Voir\" pour le visualiser ou l'imprimer";
		}
		$selected = self::cdi_get_br_selected();
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		$bordereauxdisplay = self::cdi_body_table_bordereau();
		echo wp_json_encode(
			array(
				'message' => $message,
				'htmlselected' => $selecteddisplay,
				'htmlbordereaux' => $bordereauxdisplay,
			)
		);
		wp_die();
	}

	public static function cdi_ajax_bbulklabelpdf_exec_bordereau() {
		self::cdi_clean_br_selected();
		$errorws = '';
		$refbt = date( 'YmdHis' );
		$datetimebt = date( 'Y-m-d H:i:s' );
		$carrier = get_option( 'cdi_o_Br_select_carrier' );
		$selected = self::cdi_get_br_selected();
		$selected = self::cdi_filtre_carrier_selected( $selected );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_prod_remise_format';
		$format = ( $route )();
		$return = cdi_c_Pdf_Workshop::cdi_bulk_label_pdf( $selected, $format );
		$error = $return['error'];
		$nbcolis = $return['nbcolis'];
		$resultpdf = $return['resultpdf'];
		if ( $error !== '' ) {
			$message = $error;
		} else {
			if ( $nbcolis > 0 ) {
				cdi_c_Function::cdi_stat( 'BOR-lab' );
				self::cdi_stockage_bordereau( 'LA-' . $carrier, $refbt, '=> ' . $datetimebt, $nbcolis, $resultpdf ); // '=> ' only to sure to be the first in sort
				$message = "Votre assemblage d'étquettes d'affranchissement a été généré. C'est le premier de votre liste. Cliquez sur \"Voir\" pour le visualiser ou l'imprimer";
			}
		}
		$selected = self::cdi_get_br_selected();
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		$bordereauxdisplay = self::cdi_body_table_bordereau();
		echo wp_json_encode(
			array(
				'message' => $message,
				'htmlselected' => $selecteddisplay,
				'htmlbordereaux' => $bordereauxdisplay,
			)
		);
		wp_die();
	}

	public static function cdi_ajax_bbulkcn23pdf_exec_bordereau() {
		self::cdi_clean_br_selected();
		$errorws = '';
		$refbt = date( 'YmdHis' );
		$datetimebt = date( 'Y-m-d H:i:s' );
		$carrier = get_option( 'cdi_o_Br_select_carrier' );
		$selected = self::cdi_get_br_selected();
		$selected = self::cdi_filtre_carrier_selected( $selected );
		$route = 'cdi_c_Carrier_' . $carrier . '::cdi_prod_remise_format';
		$format = ( $route )();
		$return = cdi_c_Pdf_Workshop::cdi_bulk_cn23_pdf( $selected, $format );
		$error = $return['error'];
		$nbcolis = $return['nbcolis'];
		$resultpdf = $return['resultpdf'];
		if ( $error !== '' ) {
			$message = $error;
		} else {
			if ( $nbcolis > 0 ) {
				cdi_c_Function::cdi_stat( 'BOR-cn23' );
				self::cdi_stockage_bordereau( 'CN-' . $carrier, $refbt, '=> ' . $datetimebt, $nbcolis, $resultpdf ); // '=> ' only to sure to be the first in sort
				$message = "Votre assemblage d'étquettes d'affranchissement a été généré. C'est le premier de votre liste. Cliquez sur \"Voir\" pour le visualiser ou l'imprimer";
			}
		}
		$selected = self::cdi_get_br_selected();
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		$bordereauxdisplay = self::cdi_body_table_bordereau();
		echo wp_json_encode(
			array(
				'message' => $message,
				'htmlselected' => $selecteddisplay,
				'htmlbordereaux' => $bordereauxdisplay,
			)
		);
		wp_die();
	}

	public static function cdi_ajax_bhistocsv_exec_bordereau() {
		self::cdi_clean_br_selected();
		$errorws = '';
		$carrier = get_option( 'cdi_o_Br_select_carrier' );
		$selected = self::cdi_get_br_selected();
		$selected = self::cdi_filtre_carrier_selected( $selected );
		if ( count( $selected ) > 0 ) {
			$nbcolis = count( $selected );
			$refbt = date( 'YmdHis' );
			$datetimebt = date( 'Y-m-d H:i:s' );
			$rang = 0;
			foreach ( $selected as $parcel ) {
				$rang = $rang + 1;
				$tracking = cdi_c_Function::get_string_between( $parcel, '[', ']' );
				$orderid = str_replace( ' [' . $tracking . ']', '', $parcel );
				$order = new WC_Order( $orderid );
				$ordernumber = $order->get_order_number();
				$array_for_carrier = cdi_c_Function::cdi_array_for_carrier( $orderid );
				// Open sequence
				if ( $rang == 1 ) {
					$storestringtitle = '';
					$storearraystringrow = array();
				}
				// Compute current csv header for the first line csv
				$stringtitle = '';
				foreach ( $array_for_carrier as $key => $keyvalue ) {
					$stringtitle = $stringtitle . "'" . $key . "'" . ',';
				}
				$stringtitle = str_replace( "'", '', $stringtitle );
				$stringtitle = $stringtitle . "\r\n";
				if ( strlen( $stringtitle ) > strlen( $storestringtitle ) ) {
					$storestringtitle = $stringtitle; // Choose the longest header because of variable CN23 articles
				}

				// Compute current row and store it
				$stringrow = '';
				foreach ( $array_for_carrier as $key => $keyvalue ) {
					if (is_array($keyvalue)) {
						$keyvalue = implode(' ', $keyvalue) ;
					}
					$keyvalue = trim( $keyvalue, ' ' );
					$keyvalue = str_replace( ',', ' ', $keyvalue ); // suppress , to be compatible with , csv delimiter
					$stringrow = $stringrow . $keyvalue . ',';
				}
				$stringrow = $stringrow . "\r\n";
				$storearraystringrow[] = $stringrow;
			}

			// Close sequence : Only at that point is done the real writing of csv
			cdi_c_Function::cdi_stat( 'BOR-his' );
			$resultcsv = '';
			$resultcsv .= $storestringtitle;
			foreach ( $storearraystringrow as $stringrow ) {
				$resultcsv .= $stringrow;
			}
			// End write
			$resultcsv = base64_encode( $resultcsv );
			self::cdi_stockage_bordereau( 'HI-' . $carrier, $refbt, '=> ' . $datetimebt, $nbcolis, $resultcsv ); // '=> ' only to sure to be the first in sort
		}
		if ( $errorws !== '' ) {
			$message = $errorws;
		} else {
			$message = "Votre historique csv a été généré. C'est le premier de votre liste. Cliquez sur \"Voir\" pour le visualiser ou l'imprimer";
		}
		$selected = self::cdi_get_br_selected();
		$selecteddisplay = self::cdi_get_br_displayselected( $selected );
		$bordereauxdisplay = self::cdi_body_table_bordereau();
		echo wp_json_encode(
			array(
				'message' => $message,
				'htmlselected' => $selecteddisplay,
				'htmlbordereaux' => $bordereauxdisplay,
			)
		);
		wp_die();
	}

	public static function cdi_filtre_carrier_selected( $selected ) {
		$carrier = get_option( 'cdi_o_Br_select_carrier' );
		$return = array();
		foreach ( $selected as $parcel ) {
			$tracking = cdi_c_Function::get_string_between( $parcel, '[', ']' );
			$orderid = str_replace( ' [' . $tracking . ']', '', $parcel );
			$order = wc_get_order( $orderid );
			$ordercarrier = $order->get_meta( '_cdi_meta_carrier' );
			$ordercarrier = cdi_c_Function::cdi_fallback_carrier( $ordercarrier );
			if ( $ordercarrier == $carrier ) {
				$return[] = $parcel;
			}
		}
		return $return;
	}

	public static function cdi_stockage_bordereau( $type, $reference, $date, $nbcolis, $content ) {
		cdi_c_Function::cdi_uploads_put_contents( $type . '-' . $reference, 'bordereau', $content );
		$listebordereaux = get_option( 'cdi_o_liste_bordereaux_remise' );
		if ( empty( $listebordereaux ) ) {
			$listebordereaux = array();
		}
		$listebordereaux[] = array(
			'typedocument' => $type,
			'bordereauNumber' => $reference,
			'publishingDate' => $date,
			'numberOfParcels' => $nbcolis,
		);
		// Sort and Suppress old Bordereaux
		function cmpbr( $a, $b ) {
			return strcmp( $b['publishingDate'], $a['publishingDate'] ); }
		usort( $listebordereaux, 'cmpbr' );
		$newlistbordreaux = array();
		$nbbordereaux = 0;
		$maxitemlogistic = get_option( 'cdi_o_settings_maxitemlogistic' );
		if ( ! $maxitemlogistic ) {
			$maxitemlogistic = 100;
		}
		foreach ( $listebordereaux as $bordereau ) {
			$nbbordereaux = $nbbordereaux + 1;
			if ( $nbbordereaux <= $maxitemlogistic ) {
				$newlistbordreaux[] = $bordereau;
			} else {
				// Suppress old files
				$upload_dir = wp_upload_dir();
				$dircdistore = trailingslashit( $upload_dir['basedir'] ) . 'cdistore';
				$url = wp_nonce_url( 'plugins.php?page=cdi' );
				$creds = request_filesystem_credentials( $url, '', false, false, null );
				global $wp_filesystem;
				$filename = trailingslashit( $dircdistore ) . 'CDI-' . 'bordereau' . '-' . $bordereau['typedocument'] . '-' . $bordereau['bordereauNumber'] . '.txt';
				$result = $wp_filesystem->delete( $filename );
			}
		}
		update_option( 'cdi_o_liste_bordereaux_remise', $newlistbordreaux );
		self::cdi_cdistore_purge();
	}

	public static function cdi_body_table_bordereau() {
		$listebordereaux = get_option( 'cdi_o_liste_bordereaux_remise' );
		$htmlbody = '';
		if ( empty( $listebordereaux ) ) {
			$htmlbody .= "<tr class='no-items'><td colspan='3' class='colspanchange'>Aucun document n'a été généré.</td></tr>";
		} else {
			foreach ( $listebordereaux as $bordereau ) {
				$tdtitrebordereau = '';
				foreach ( $bordereau as $key => $value ) {
					$tdtitrebordereau .= ' | ' . $key . '=>' . $value;
				}
				$datebordereau = str_replace( 'T', ' ', $bordereau['publishingDate'] );
				$tdvoirbordereau = '<td style="overflow:hidden; white-space:nowrap; text-overflow: ellipsis;"><form method="post" id="cdi_bordereau_voir" action="" style="display:inline-block;"><input type="hidden" name="cdi_bordereau_voir_post" value="' . $bordereau['typedocument'] . '-' . $bordereau['bordereauNumber'] . '"><input type="submit" name="cdi_bordereau_voir" value="Voir"  title="Print bordereau' . $tdtitrebordereau . '" /></form></td>';
				$htmlbody .= '<tr><td>' . $datebordereau . '</td><td>' . $bordereau['typedocument'] . '-' . $bordereau['bordereauNumber'] . '</td><td>' . $bordereau['numberOfParcels'] . '</td>' . $tdvoirbordereau . '</tr>';
			}
		}
		return $htmlbody;
	}

	public static function cdi_bordereau_voir() {
		if ( isset( $_POST['cdi_bordereau_voir'] ) ) {
			global $woocommerce;
			global $wpdb;
			$bordereau_id = sanitize_text_field( $_POST['cdi_bordereau_voir_post'] );
			if ( current_user_can( 'cdi_gateway' ) ) {
				$cdi_locbordereau = cdi_c_Function::cdi_uploads_get_contents( $bordereau_id, 'bordereau' );
				if ( $cdi_locbordereau ) {
					$typeoutput = substr( $bordereau_id, 0, 2 );
					if ( in_array( $typeoutput, array( 'BT', 'LP', 'EL', 'BD', 'LA', 'CN' ) ) ) {
						$cdi_file_content = base64_decode( $cdi_locbordereau );
						$out = fopen( 'php://output', 'w' );
						$thefile = 'Bordereau-' . $bordereau_id . '-' . date( 'YmdHis' ) . '.pdf';
						header( 'Content-Type: application/pdf' );
						header( 'Content-Disposition: attachment; filename=' . $thefile );
						fwrite( $out, $cdi_file_content );
						fclose( $out );
						die();
					}
					if ( in_array( $typeoutput, array( 'HI' ) ) ) {
						$cdi_file_content = base64_decode( $cdi_locbordereau );
						$out = fopen( 'php://output', 'w' );
						$thefile = 'Bordereau-' . $bordereau_id . '-' . date( 'YmdHis' ) . '.csv';
						header( 'Content-Type: application/csv' );
						header( 'Content-Disposition: attachment; filename=' . $thefile );
						fwrite( $out, $cdi_file_content );
						fclose( $out );
						die();
					}
				}
			} // End current_user_can
		}
	}

	public static function cdi_cdistore_purge() {
	return ;
		// Clean cdistore of oldest files : label, cn23, and bordereau
		$maxdaycdistore = get_option( 'cdi_o_settings_maxdaycdistore' );
		if ( $maxdaycdistore && $maxdaycdistore != '' && is_numeric( $maxdaycdistore ) && $maxdaycdistore >= 1 ) {
			$time = time();
			$timeforpurge = $time - ( $maxdaycdistore * 86400 );
			$upload_dir = wp_upload_dir();
			$dircdistore = trailingslashit( $upload_dir['basedir'] ) . 'cdistore';
			$url = wp_nonce_url( 'plugins.php?page=cdi' );
			$creds = request_filesystem_credentials( $url, '', false, false, null );
			global $wp_filesystem;		
			$arraydirfiles = $wp_filesystem->dirlist( $dircdistore );
			foreach ( $arraydirfiles as $arraydirfile ) {
				$filename = $arraydirfile['name'];
				$filelastmodunix = $arraydirfile['lastmodunix'];
				$deltadays = ( $time - $filelastmodunix ) / 86400;
				if ( $filelastmodunix < $timeforpurge ) {
					$filetodelete = trailingslashit( $dircdistore ) . $filename;
					$r = $wp_filesystem->delete( $filetodelete );
					cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Cdistore Purge deletion of file ' . $filename . ' - Age in days : ' . floor( $deltadays ), 'msg' );
				}
			}
		}
	}

}


?>
