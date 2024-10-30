<?php
/**
 * This file is part of the CDI (Collect and Deliver Interface) plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/****************************************************************************************/
/* CDI uninstall proc                                                                   */
/****************************************************************************************/

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;}
class cdi_Uninstall {

	function __construct() {
		global $wpdb;
		// check if it is a multisite uninstall - if so, run the uninstall function for each blog id
		if ( is_multisite() ) {
			foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $blog_id ) {
				  switch_to_blog( $blog_id );
				  $this->uninstall();
			}
			restore_current_blog();
		} else {
			$this->uninstall();
		}
	}

	function uninstall() {
		global $wpdb;
		if ( get_option( 'cdi_o_settings_cleanonsuppress' ) == 'yes' ) {
			$wpdb->query( "delete from $wpdb->options where option_name LIKE 'CDI\_O\_%';" );
		}
	}
}

new cdi_Uninstall();
