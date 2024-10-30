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
/* Add bulk CDI action in the WC orders listing (to add parcels in Gateway)             */
/****************************************************************************************/
class cdi_c_Orderlist_Bulkactions {
	public static function init() {
		add_action( 'admin_footer-edit.php', __CLASS__ . '::cdi_wcorderlist_bulk_action_declare' );
		add_filter( 'woocommerce_bulk_action_ids', __CLASS__ . '::cdi_wcorderlist_bulk_action_exec', 10, 2 );		
	}
	
	public static function cdi_wcorderlist_bulk_action_declare() {
		// Nothing more to do
	}
	
	public static function cdi_wcorderlist_bulk_action_exec($ids, $action) {
		if(($action == 'cdi_action_wcorderlist') and is_array($ids)) {
			$nbcolis = 0;
			cdi_c_Gateway::cdi_c_Addgateway_open();
			foreach ( $ids as $id ) {
				$ret= cdi_c_Gateway::cdi_c_Addgateway_add( $id );
				if ($ret === true) {
					$nbcolis++;
				}
			}
			cdi_c_Gateway::cdi_c_Addgateway_close();
			if ( $nbcolis > 0 ) {
				$message = number_format_i18n( $nbcolis ) . ' parcels (from orders) added in Gateway.';
				update_option( 'cdi_o_notice_display', $message );
			}
		}
		return $ids;
	}
}

