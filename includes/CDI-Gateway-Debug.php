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
/* Add Debug actions in the Gateway                                 */
/****************************************************************************************/
class cdi_c_Gateway_Debug {
	public static function init() {
		add_action( 'wp_ajax_cdi_debug_open_view', __CLASS__ . '::cdi_ajax_debug_open_view' );
		add_action( 'wp_ajax_cdi_debug_close_view', __CLASS__ . '::cdi_ajax_debug_close_view' );
		add_action( 'wp_ajax_cdi_debug_clear_file', __CLASS__ . '::cdi_ajax_debug_clear_file' );
		add_action( 'wp_ajax_cdi_debug_refresh_view', __CLASS__ . '::cdi_ajax_debug_refresh_view' );
	}
	/**
	 * Manage the Debug function.
	 */
	public static function debug_manage() {
		?>
		<div id="debugmanagewrap">
		  <div id="debugmanage" style="display:none;">
			<h2>CDI Debug</h2>
			<p>
			  <input type="button" id="cdi-debug-close" class="button button-primary mode-run" value="<?php _e( 'Close', 'cdi' ); ?>" />
			  <input type="button" id="cdi-debug-clear" class="button button-primary" value="<?php _e( 'Suppress cdilog.log', 'cdi' ); ?>" />
			  <input type="button" id="cdi-debug-refresh" class="button button-primary" value="<?php _e( 'Refresh view', 'cdi' ); ?>" />
			  <!-- <input type="button" id="cdi-debug-select" class="button button-primary" value="<?php _e( 'Select Debug View', 'cdi' ); ?>" /> -->
			  <a> </a><?php _e( ' Select Debug View ==>', 'cdi' ); ?> 
				  <select name="cdi-debug-select" id="cdi-debug-select">
					<option value="cdi">CDI - Tous les messages cdilog.log</option>
					<option value="exp">CDI - Erreurs d'exploitation (exp)</option>
					<option value="tec">CDI - Incidents techniques (tec)</option>
					<option value="msg">CDI - Messages de suivi d'exploitation (msg)</option>
					<option value="debug.log">Messages de debug.log</option>
					<option value="error_log">Messages de error_log</option>                    
				  </select> 
			  </p>
			<textarea id="cdi-debug-area" style="font-size: 14px;width: 98%; height:calc(100vh - 50px);"></textarea>
		  <!-- End of div id="debugmanage" -->
		  </div>
		</div> 
		<?php
	}
	public static function cdi_debug_open_button() {
		$cdi_o_Save_init_set = get_option( 'cdi_o_Save_init_set' );
		if ( ! $cdi_o_Save_init_set ) {
			$color = '#0085ba'; // debug is close
		} else {
			$color = 'red'; // debug is running
		}
		?>
	  <em></em><input type="button" id="cdi-debug-open" class="mode-run" value="<?php _e( 'Debug View', 'cdi' ); ?>" style="float: left; background-color: <?php echo esc_attr( $color ); ?>; color:white;" title="<?php _e( 'Debug CDI. To open click!', 'cdi' ); ?>" /><em></em><?php
		$ajaxurl = admin_url( 'admin-ajax.php' );
	}
	public static function cdi_ajax_debug_open_view() {
		cdi_c_Function::cdi_stat( 'DEB-ope' );
		$cdi_o_Save_init_set = get_option( 'cdi_o_Save_init_set' );
		if ( ! $cdi_o_Save_init_set ) {
			update_option( 'cdi_o_Save_init_set', 'yes' );
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Debug CDI ouvert !', 'msg' );
		}
		self::cdi_ajax_debug_refresh_view();
		wp_die();
	}
	public static function cdi_ajax_debug_close_view() {
		$cdi_o_Save_init_set = get_option( 'cdi_o_Save_init_set' );
		if ( $cdi_o_Save_init_set ) {
			cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Debug CDI fermé !', 'msg' );
			delete_option( 'cdi_o_Save_init_set' );
		}
		wp_die();
	}
	public static function cdi_ajax_debug_clear_file() {
		file_put_contents( WP_CONTENT_DIR . '/cdilog.log', '' );
		cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Debug CDI purgé !', 'msg' );
		_e( 'The cdilog.log has been cleared.', 'cdi' );
		wp_die();
	}
	public static function cdi_ajax_debug_refresh_view() {
		cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Debug CDI rafraichi !', 'msg' );
		if ( ! file_exists( WP_CONTENT_DIR . '/cdilog.log' ) ) {
			$file = fopen( WP_CONTENT_DIR . '/cdilog.log', 'w' );
			if ( ! $file ) {
				cdi_c_Function::cdi_debug( __LINE__, __FILE__, 'Cannot create cdilog.log!', 'tec' );
				die( 'Cannot create cdilog.log!' );
			}
			fwrite( $file, '' );
			fclose( $file );
		}
		$result = '';
		$sep = '$!$';
		$select = sanitize_text_field( $_POST['select'] );
		if ( $select == 'debug.log' ) {
			$filelog = '/debug.log';
		} elseif ( $select == 'error_log' ) {
			$filelog = '/../error_log';
		} else {
			$filelog = '/cdilog.log';
		}
		if ( file_exists( WP_CONTENT_DIR . $filelog ) ) {
			$file = @fopen( WP_CONTENT_DIR . $filelog, 'r' );
			$entries = array();
			$currentline = '';
			while ( ( $line = @fgets( $file ) ) !== false ) {
				$lineparts = preg_replace( '/^\[([0-9a-zA-Z-]+) ([0-9:]+) ([a-zA-Z_\/]+)\] (.*)$/i', '$1' . $sep . '$2' . $sep . '$3' . $sep . '$4', $line );
				$parts = explode( $sep, $lineparts );
				if ( count( $parts ) >= 4 ) {
					if ( $currentline !== '' ) {
						$entries[] = $currentline;
						$currentline = '';
					}
					$currentline .= $line;
				} else {
					$currentline .= $line;
				}
			}
			if ( $currentline !== '' ) {
				$entries[] = $currentline;
			}
			if ( $select == 'cdi' ) {
				$patternesearch = '*** LOG CDI';
			} elseif ( $select == 'exp' ) {
				$patternesearch = '*** LOG CDI(exp)';
			} elseif ( $select == 'tec' ) {
				$patternesearch = '*** LOG CDI(tec)';
			} elseif ( $select == 'msg' ) {
				$patternesearch = '*** LOG CDI(msg)';
			} else {
				$patternesearch = ']';
			}
			foreach ( $entries as $item ) {
				if ( strpos( $item, $patternesearch ) ) {
					$result .= $item;
				}
			}
			fclose( $file );
		}
		echo  htmlspecialchars($result, ENT_QUOTES) ;
		wp_die();
	}

}
?>
