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
/* Collect point Follow tracking                                                        */
/****************************************************************************************/

class cdi_c_Collect_Follow {
	public static function init() {
		add_action( 'wp_ajax_cdi_collect_follow', __CLASS__ . '::cdi_Collect_callback_follow' );
		add_action( 'wp_ajax_nopriv_cdi_collect_follow', __CLASS__ . '::cdi_Collect_callback_follow' );
		add_action( 'wp_ajax_cdi_collect_delivered', __CLASS__ . '::cdi_Collect_callback_delivered' );
		add_action( 'wp_ajax_nopriv_cdi_collect_delivered', __CLASS__ . '::cdi_Collect_callback_delivered' );
	}
	
	public static function cdi_Collect_callback_header() {
		echo '<!DOCTYPE html> <html> <body> <div style="display:inline-block; margin: 10px; padding:10px; border:1px solid black; background-color:#eafafb; width:98%; height:95%">';	
	}
	
	public static function cdi_Collect_callback_trailer() {
		echo '</div> </body> </html>';	
	}		

	public static function cdi_Collect_callback_banner() {
		$urlcollectlogo = plugins_url( '../images/logocollectmerchant.png', dirname( __FILE__ ) );
		$urlcollectlogo = apply_filters( 'cdi_filterurl_collect_logo', $urlcollectlogo );
		$html = '<div style="display:inline-block; margin: 10px; padding: 10px;"><img style="display:inline-block;" src="' . $urlcollectlogo . '"></div>';
		$html .= '<div style="display:inline-block; margin: 10px; padding: 10px;"><p style="font: small-caps bold 90px/1 sans-serif;">' . get_option( 'blogname' ) . '</p><p style="font: small-caps bold 40px/1 sans-serif;"> ' . get_option( 'blogdescription' ) . '</p></div>';
		$html = apply_filters( 'cdi_filterhtml_collect_banner', $html ); // For Customisation by e-Merchant
		echo wp_kses_post( $html );
	}

	public static function cdi_Collect_callback_follow() {
		$trackingcode = sanitize_text_field( $_GET['trk'] );
		$msgsuivicolis = null;
		$contractnumber = get_option( 'cdi_installation_id' );
		if ( $trackingcode && $contractnumber ) {
			$splittracking = str_replace( 'C', '', $trackingcode );
			$splittracking = str_replace( 'I', '', $splittracking );
			$splittracking = explode( 'D', $splittracking );
			if ( $splittracking['0'] && $splittracking['1'] && ( $contractnumber == $splittracking['0'] ) ) {
				$track_order_id = $splittracking['1'];
				$order = wc_get_order($track_order_id );
				$trackingstatus = $order->get_meta( '_cdi_meta_collect_status' );
				if ( $trackingstatus ) {
					$lib_status = str_replace( array( 'preparation', 'atcollectpoint', 'courier', 'delivered', 'customeragreement' ), array( __( 'In preparation for', 'cdi' ), __( 'At collect point', 'cdi' ), __( 'Courier is running', 'cdi' ), __( 'Delivered to customer', 'cdi' ), __( 'Customer agreement', 'cdi' ) ), $trackingstatus );
					$msgsuivicolis = '=> ' . $lib_status;
				}
			}
		}
		self::cdi_Collect_callback_header();
		self::cdi_Collect_callback_banner();
		if ( $msgsuivicolis ) {
			$msgcollectfollow = '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'Your parcel status for ', 'cdi' ) . esc_html( $trackingcode ) . ' ' . $msgsuivicolis . '</a></p></div>';
		} else {
			$msgcollectfollow = 'div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'Tracking code not correct : ', 'cdi' ) . esc_html( $trackingcode ) . '</a></p></div>';
		}
		echo '<div style="margin:10px; padding:10px; border:1px solid black; background-color:white; position:fixed; top:45vh; left:9vw; width:80vw; height:10vh">' . wp_kses_post( $msgcollectfollow ) . '</div>';
		self::cdi_Collect_callback_trailer();
		wp_die();
	}

	public static function cdi_Collect_callback_delivered() {
		global $woocommerce;
		// Adapting POST to GET if any
		if ( isset( $_POST['trk'] ) ) {
			$_GET['trk'] = sanitize_text_field( $_POST['trk'] );
		}
		if ( isset( $_POST['act'] ) ) {
			$_GET['act'] = sanitize_text_field( $_POST['act'] );
		}
		if ( isset( $_POST['securitycode'] ) ) {
			$_GET['securitycode'] = sanitize_text_field( $_POST['securitycode'] );
		}		
		$trackingcode = sanitize_text_field( $_GET['trk'] );
		$msgsuivicolis = null;
		$track_order_id = null;
		$typesecuritycode = null;
		$messerror = null;
		for ( $i = 1; $i <= 1; $i++ ) { // Bloc for break
			// Case Annulation of page
			if ( isset( $_GET['act'] ) and sanitize_text_field( $_GET['act'] ) == 'annu' ) {
				?>
				<html>
		  		<body onload="self.close()">
		  		</body>
				</html>
				<?php
			}
			$contractnumber = get_option( 'cdi_installation_id' );
			if ( $trackingcode && $contractnumber ) {
				$splittracking = str_replace( 'C', '', $trackingcode );
				$splittracking = str_replace( 'I', '', $splittracking );
				$splittracking = explode( 'D', $splittracking );
				if ( $splittracking['0'] && $splittracking['1'] && ( $contractnumber == $splittracking['0'] ) ) {
					$track_order_id = $splittracking['1'];
					$order = wc_get_order($track_order_id );
					$trackingstatus = $order->get_meta( '_cdi_meta_collect_status' );
					if ( $trackingstatus ) {
						$lib_status = str_replace( array( 'preparation', 'atcollectpoint', 'courier', 'delivered', 'customeragreement' ), array( __( 'In preparation for', 'cdi' ), __( 'At collect point', 'cdi' ), __( 'Courier is running', 'cdi' ), __( 'Delivered to customer', 'cdi' ), __( 'Customer agreement', 'cdi' ) ), $trackingstatus );
						$msgsuivicolis = '=> ' . $lib_status;
					}
				}
			}
			$order = wc_get_order( $track_order_id );
			// Case $trackingcode passed is not valid
			if ( ! $track_order_id or ! $trackingstatus or $trackingstatus == '' or ! $order or ! $msgsuivicolis ) {
				self::cdi_Collect_callback_header();
				self::cdi_Collect_callback_banner();
				$html = '<div id= "cdicollectzone" style="margin:10px; padding:10px; border:1px solid black; background-color:white; position:fixed; top:40vh; left:9vw; width:80vw;">';
				$html .= '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'Tracking code not correct : ', 'cdi' ) . esc_html( $trackingcode ) . '</a></p></div>';
				$html .= '</div>';
				echo wp_kses_post( $html );
				self::cdi_Collect_callback_trailer();
				die;
				break;
			}
			$typesecuritycode = $order->get_meta( '_cdi_meta_securitymode' );
			$trackingstatus = $order->get_meta( '_cdi_meta_collect_status' );
			// Case Still In preparation
			if ($trackingstatus == 'preparation') {
				self::cdi_Collect_callback_header();
				self::cdi_Collect_callback_banner();
				$html = '<div id= "cdicollectzone" style="margin:10px; padding:10px; border:1px solid black; background-color:white; position:fixed; top:40vh; left:9vw; width:80vw;">';
				$html .= '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'Your order is still in preparation stage. <br> You must wait a small bit to get it.', 'cdi' ) . '</a></p></div>';
				$html .= '</div>';
				echo wp_kses_post( $html );
				self::cdi_Collect_callback_trailer();
				die;
				break;
			}
			// Case order has already received a customer agreement
			if ($trackingstatus == 'customeragreement') {
				self::cdi_Collect_callback_header();
				self::cdi_Collect_callback_banner();
				$html = '<div id= "cdicollectzone" style="margin:10px; padding:10px; border:1px solid black; background-color:white; position:fixed; top:40vh; left:9vw; width:80vw;">';
				$html .= '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'Your order has already been acknowledged as received. <br>  You can not do more and we wish you to enjoy it. <br>  You are welcome.', 'cdi' ) . '</a></p></div>';
				$html .= '</div>';
				echo wp_kses_post( $html );
				self::cdi_Collect_callback_trailer();
				die;
				break;
			}

			// Case of first display of page
			if ( ! isset( $_GET['act'] ) ) {
				?><script type="text/javascript" src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script><?php
				$ajaxurl = admin_url( 'admin-ajax.php' );
				?><script>
					var ajaxurl = "<?php echo $ajaxurl; ?>"; 
					function cdicollectconfirm() {
			  			if (!document.getElementById("cdi_collect_deliv_entry")){
							var data = {'action': 'cdi_collect_delivered', 'trk': '<?php echo esc_html( $trackingcode ); ?>', 'act': 'conf' } ;    
			 			}else{         
							var securitycode = document.getElementById("cdi_collect_deliv_entry").value ;
							var data = {'action': 'cdi_collect_delivered', 'trk': '<?php echo esc_html( $trackingcode ); ?>', 'act': 'conf', 'securitycode':  securitycode } ;
			 			}
			 			jQuery.post(ajaxurl, data, function(response) {jQuery("#cdicollectzone").html(response) ;});
					}
		  		</script><?php
				self::cdi_Collect_callback_header();
				self::cdi_Collect_callback_banner();							
				$buttonannul = '<a id="cdi_collect_annul" name="cdi_collect_annul" style="background-color:#0085ba; color:white; font-weight:bold; margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif; float:left;" title="Close" href="' . admin_url( 'admin-ajax.php' ) . '?action=cdi_collect_delivered&trk=' . esc_html( $trackingcode ) . '&act=annu' . '" >' . __( 'Close', 'cdi' ) . '</a>' ;		
				$buttondelivered = '<button id="cdi_collect_deliv" onclick="cdicollectconfirm()" name="cdi_collect_deliv" style="background-color:#0085ba; color:white; font-weight:bold; margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif; float:right;" title="' . __( 'Confirm the marking of the order as delivered.', 'cdi' ) . '" >' . __( 'To confirm', 'cdi' ) . '</button>' ;
				$inputsecuritycode = '<input id="cdi_collect_deliv_entry" name="cdi_collect_deliv_entry" style="width:15vw; height:45px; border:1px solid black; background-color:white; color:black; font-weight:bold; margin: 10px; padding: 10px; font: bold 24px/1 sans-serif; float:right;" placeholder="Security code" title=" ' . __( 'Enter your security code', 'cdi' ) . '"></input>' ;				
				if ( $typesecuritycode == 'free' ) {	
					echo '<div id= "cdicollectzone" style="margin:10px; padding:10px; border:1px solid black; background-color:white; position:fixed; top:40vh; left:9vw; width:80vw;">';
					echo '<div style="width:60vw; height:9vh; margin-left:auto; margin-right:auto;">' . $buttonannul . $buttondelivered . '</div>';
					echo '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'To switch to delivered / received the Order ', 'cdi' ) . esc_html( $trackingcode ) . __( ' click on the "Confirm" button.', 'cdi' ) . '</a></p></div>';
					echo '<div style="margin: 10px; padding: 10px;"><p><a>' . __( 'Current tracking Status of the Order ', 'cdi' ) . esc_html( $trackingcode ) . ' <strong>' . $msgsuivicolis . '</strong></a></p></div>';
					echo '</div>' ; 					
				} else { // applysecurity :  So totalprice or deliveredcode
					echo '<div id= "cdicollectzone" style="margin:10px; padding:10px; border:1px solid black; background-color:white; position:fixed; top:40vh; left:9vw; width:80vw;">';
					echo '<div style="width:60vw; height:9vh; margin-left:auto; margin-right:auto;">' . $buttonannul . $inputsecuritycode . $buttondelivered .  '</div>';
					echo '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'To switch to delivered / received the Order ', 'cdi' ) . esc_html( $trackingcode ) . __( ' enter your security code (*) then click on the "Confirm" button.', 'cdi' ) . '</a></p></div>';
					echo '<div style="margin: 10px; padding: 10px;"><p><a>' . __( 'Current tracking Status of the Order ', 'cdi' ) . esc_html( $trackingcode ) . ' <strong>'  . $msgsuivicolis . '</strong></a></p></div>';
					echo '<div style="margin: 10px; padding: 10px; font-size: 0.7em;"><p><a>' . __( '(*) As customer, your security code is the total price you have paid for your order, expressed in cents (i.e. 1550 if you paid 15,50€) .', 'cdi' ) . '</a></p></div>';
					echo '</div>' ; 
				}			
				self::cdi_Collect_callback_trailer();			
				cdi_c_Function::cdi_stat( 'CAC-deli' );
				die;
				break;
			}
			// Case it is the confirmation of stage above
			if ( sanitize_text_field( $_GET['act'] ) == 'conf' ) {
				if ( isset( $_GET['securitycode'] ) ) {
					$code = sanitize_text_field( $_GET['securitycode'] );
					$cdi_meta_deliveredcode = $order->get_meta( '_cdi_meta_deliveredcode' );
					$totalprice = $order->get_total() * 100;
					if ($code == $totalprice ) {					
						$order->update_meta_data( '_cdi_meta_collect_status', 'customeragreement' );
						$html = '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'Switching to the "Customer agreement" tracking status of the Order ', 'cdi' ) . esc_html( $trackingcode ) . '</a></p></div>';					
					}elseif($code == $cdi_meta_deliveredcode){
						$order->update_meta_data( '_cdi_meta_collect_status', 'delivered' );					
						$html = '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'Switching to the "Delivered to customer" tracking status of the Order ', 'cdi' ) . esc_html( $trackingcode ) . '</a></p></div>';	
					}else{
						$html = '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'Security code is incorrect. Please scan again the QRcode and try again.', 'cdi' ) . '</a></p></div>';					
					}
					$order->save();
				} else {
					$order->update_meta_data( '_cdi_meta_collect_status', 'delivered' );
					$order->save();
					$html = '<div style="margin: 10px; padding: 10px; font: small-caps bold 24px/1 sans-serif;"><p><a>' . __( 'Switching to the "Delivered to customer" tracking status of the Order ', 'cdi' ) . esc_html( $trackingcode ) . '</a></p></div>';
				}
				echo wp_kses_post( $html );
				cdi_c_Function::cdi_stat( 'CAC-conf' );
				die;
				break;
			}
		} // End for
	}


}
?>
