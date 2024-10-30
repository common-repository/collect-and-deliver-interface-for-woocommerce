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
  /* CDI Edit Collect Points                                                              */
  /****************************************************************************************/

class cdi_c_Collect_Points {
	public static function init() {
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::cdi_admin_enqueue_scripts_collect_points_edit' );
	}

	public static function cdi_admin_enqueue_scripts_collect_points_edit( $hook_suffix ) {
	}

	public static function cdi_admin_collect_points_edit() {
		global $message;
		global $arraccesspoints;
		$arraccesspoints = get_option( 'cdi_o_settings_collect_pointslist' );

		// To get the startfile if table empty
		if ( ! $arraccesspoints or count( $arraccesspoints ) == 0 ) {
			$cdi_collect_defautfile = get_option( 'cdi_o_settings_collect_defautpointsfile' );
			if ( ! $cdi_collect_defautfile ) {
				$cdi_collect_defautfile = 'CDI-collect-defaut.php';
			}
			include( plugin_dir_path( __FILE__ ) . '../../uploads/' . $cdi_collect_defautfile );
			if ( ! isset( $startfile ) ) {
				echo '<div class="updated notice"><p>';
				echo __( 'This collect starting file in cdi/uploads is not valid : ', 'cdi' ) . esc_attr( $cdi_collect_defautfile );
				echo '</p></div>';
				$return = '';
			} else {
				$arraccesspoints = $startfile;
				update_option( 'cdi_o_settings_collect_pointslist', $arraccesspoints );
			}
		}

		// To update the current Collect point which is open
		$pointtoupdate = get_option( 'cdi_o_settings_section-collect-mod-ref' );
		if ( $pointtoupdate ) {
			$newarray = array();
			foreach ( $arraccesspoints as $collectpoint ) {
				if ( $collectpoint['ref'] == $pointtoupdate ) {
					$collectpoint['id'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-id' ) );
					$collectpoint['name'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-name' ) );
					$collectpoint['adl1'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-adl1' ) );
					$collectpoint['adl2'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-adl2' ) );
					$collectpoint['adl3'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-adl3' ) );
					$collectpoint['adcp'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-adcp' ) );
					$collectpoint['adcity'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-adcity' ) );
					$collectpoint['adcodcountry'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-adcodcountry' ) );
					$collectpoint['phone'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-phone' ) );
					$collectpoint['parking'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-parking' ) );
					$collectpoint['indice'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-indice' ) );
					$collectpoint['lat'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-lat' ) );
					$collectpoint['lon'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-lon' ) );
					$collectpoint['horomon'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-horomon' ) );
					$collectpoint['horotue'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-horotue' ) );
					$collectpoint['horowed'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-horowed' ) );
					$collectpoint['horothu'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-horothu' ) );
					$collectpoint['horofri'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-horofri' ) );
					$collectpoint['horosat'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-horosat' ) );
					$collectpoint['horosun'] = sanitize_text_field( get_option( 'cdi_o_settings_section-collect-mod-horosun' ) );
				}
				$newarray[] = $collectpoint;
			}
			cdi_c_Function::cdi_stat( 'CAC-poi' );
			$arraccesspoints = $newarray;
			delete_option( 'cdi_o_settings_section-collect-mod-ref' );
			update_option( 'cdi_o_settings_collect_pointslist', $arraccesspoints );
		}
		$msgcountpoints = "<a id='cdi-count-point' style='color:black;'> " . count( $arraccesspoints ) . " </a><a id='cdi-textcount-points' style='color:black;'>" . __( ' Collect point(s).', 'cdi' ) . '</a>';
		echo wp_kses_post( $msgcountpoints );

		// An Open collect point has been done
		if ( isset( $_POST['cdi_collectpoint_edit'] ) and isset( $_POST['cdi_collectpoint_edit_post'] ) ) { // Edit requested
			self::cdi_admin_collect_points_modif();
		}

		?>
<div class="wrap">
<div id="collectpointsedit">
<div id="poststuff">
  <div class="metabox-holder columns-2" id="post-body">
	<!-- ************************************************************************************************** -->
	<div id="post-body-content">
	  <form method="post" action="?page=<?php echo esc_js( esc_html( sanitize_text_field( $_GET['page'] ) ) ); ?>">
		<?php self::cdi_collectpoint_remove(); ?>
		<?php self::cdi_collectpoint_add(); ?>        
		<?php
		if ( isset( $message ) ) {
			echo '<div style="padding: 5px;" class="updated"><p>' . esc_attr( $message ) . '</p></div>'; }
		?>
		<div id="outer" style="position: relative;">
		  <div id="inner" style="overflow: auto; max-height:25vh;">
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
		  <input onclick="javascript: window.onbeforeunload = null; return confirm('<?php _e( 'Are you sure you want to delete ?', 'cdi' ); ?>');" name="cdi_collectpoint_remove" type="submit" value="<?php _e( 'Remove collect point', 'cdi' ); ?>" style="background-color:#0085ba; color:white; font-weight:bold;" /> 
		  <input name="cdi_collectpoint_add" type="submit" value="<?php _e( 'Add collect point', 'cdi' ); ?>" style="float: right; background-color:#0085ba; color:white; font-weight:bold;" />            
		  
		  <em></em>
		</p>

	  </form>
	  <em></em>                            
	  <!-- ************************************************************************************************** -->
	  <div class="meta-box-sortables">
	  </div>
	</div>
	<!-- ************************************************************************************************** -->
  </div>
  <br class="clear">
</div>
		<?php
	}

	public static function cdi_collectpoint_remove() {
		global $message;
		global $arraccesspoints;
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_collectpoint_remove'] ) ) {
			if ( isset( $_GET['rem'] ) ) {
				$_POST['rem'][] = sanitize_text_field( $_GET['rem'] );
			}
			$count = 0;
			if ( isset( $_POST['rem'] ) && is_array( $_POST['rem'] ) ) {
				foreach ( $_POST['rem'] as $id ) {
					$newarray = array();
					foreach ( $arraccesspoints as $collectpoint ) {
						if ( $collectpoint['id'] != $id ) {
							$newarray[] = $collectpoint;
						}
					}
					$arraccesspoints = $newarray;
					update_option( 'cdi_o_settings_collect_pointslist', $arraccesspoints );
					$count++;
				}
				$message = $count . __( ' Collect points have been removed successfully.', 'cdi' );
			}
		}
	}

	public static function cdi_collectpoint_add() {
		global $message;
		global $arraccesspoints;
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' and isset( $_POST['cdi_collectpoint_add'] ) ) {
			$arraccesspoints[] = array(
				'ref' => time(),
				'id' => date( 'His' ),
				'name' => ' ===> New Collect point to fill',
				'adl1' => '',
				'adl2' => '',
				'adl3' => '',
				'adcp' => '',
				'adcity' => '',
				'adcodcountry' => '',
				'phone' => '',
				'parking' => '',
				'indice' => '',
				'lat' => '',
				'lon' => '',
				'horomon' => '',
				'horotue' => '',
				'horowed' => '',
				'horothu' => '',
				'horofri' => '',
				'horosat' => '',
				'horosun' => '',
			);
			update_option( 'cdi_o_settings_collect_pointslist', $arraccesspoints );
			$message = '1' . __( ' Collect point have been added successfully.', 'cdi' );
		}
	}

	public static function cdi_headfoot_table() {
		?>
<tr>
  <th class="manage-column column-cb check-column" id="cb" scope="col" style="width:1%;"><input type="checkbox"></th>
  <th class="manage-column column-id" id="cdi-collectpoint-ref" scope="col" style="width:2%;"><span class="sorting-indicator"></span></th> 
  <th class="manage-column column-id" id="cdi-collectpoint-id" scope="col" style="width:3%;"><?php _e( 'Collect Point Id', 'cdi' ); ?><span class="sorting-indicator"></span></th>
  <th class="manage-column column-name" id="cdi-collectpoint-name" scope="col"><span><?php _e( 'Collect Point Name', 'cdi' ); ?></span><span class="sorting-indicator"></span></th>
  <th class="manage-column column-edit" id="cdi-collectpoint-edit" scope="col" style="width:3%;"><span></span><span class="sorting-indicator"></span></th>
</tr>
		<?php
	}

	public static function cdi_body_table() {
		global $message;
		global $arraccesspoints;
		function cmp( $a, $b ) {
			$r = -1;
			if ( $b['id'] > $a['id'] ) {
				$r = 1;
			} return $r; }
		usort( $arraccesspoints, 'cmp' );
		if ( count( $arraccesspoints ) < 1 ) {
			echo '<tr class="no-items"><td colspan="3" class="colspanchange">' . __( 'No Collect points have been registered.', 'cdi' ) . '</td></tr>';
		} else {
			foreach ( $arraccesspoints as $row ) {
				$color = '#ffffff';
				echo '<tr style="background-color:' . esc_attr( $color ) . ';"> 
                  <th class="check-column" style="padding:5px 0 2px 0"><input type="checkbox" name="rem[]" value="' . esc_js( esc_html( $row['id'] ) ) . '"></th>
                  <td><a title="Collect Point Ref"  style="color:gray;"/>' . esc_js( esc_html( $row['ref'] ) ) . '</a></td>                  
                  <td><a title="Collect Point Id" />' . esc_js( esc_html( $row['id'] ) ) . '</a></td>
                  <td><a title="Collect Point Name" />' . esc_js( esc_html( $row['name'] ) ) . '</a></td>  
                  <td><form method="post" id="cdi_collectpoint_edit" action="" style="display:inline-block;"><input type="hidden" name="cdi_collectpoint_edit_post" value="' . esc_js( esc_html( $row['ref'] ) ) . '"><input type="submit" name="cdi_collectpoint_edit" value="Open"  title="Open Collect point" /></form></td>                   
                </tr>';
			}
		}
	}

	public static function cdi_admin_collect_points_modif() {
		global $message;
		global $arraccesspoints;
		?>
		  
<div class="wrap">
<div id="collectpointsedit">
<div id="poststuff">
  <div class="metabox-holder columns-2" id="post-body">
	<!-- ************************************************************************************************** -->
	<div id="post-body-content">
	  <form method="post" action="?page=<?php echo esc_js( esc_html( sanitize_text_field( $_GET['page'] ) ) ); ?>">
		<div id="outer" style="position: relative;">
		  <div id="inner" style="overflow: auto; max-height:40vh;">
		<?php
		foreach ( $arraccesspoints as $row ) {
			if ( $_POST['cdi_collectpoint_edit_post'] == $row['ref'] ) {
				update_option( 'cdi_o_settings_section-collect-mod-ref', sanitize_text_field( $row['ref'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-id', sanitize_text_field( $row['id'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-name', sanitize_text_field( $row['name'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-adl1', sanitize_text_field( $row['adl1'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-adl2', sanitize_text_field( $row['adl2'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-adl3', sanitize_text_field( $row['adl3'] ) );

				update_option( 'cdi_o_settings_section-collect-mod-adcp', sanitize_text_field( $row['adcp'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-adcity', sanitize_text_field( $row['adcity'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-adcodcountry', sanitize_text_field( $row['adcodcountry'] ) );

				update_option( 'cdi_o_settings_section-collect-mod-phone', sanitize_text_field( $row['phone'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-parking', sanitize_text_field( $row['parking'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-indice', sanitize_text_field( $row['indice'] ) );

				update_option( 'cdi_o_settings_section-collect-mod-lat', sanitize_text_field( $row['lat'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-lon', sanitize_text_field( $row['lon'] ) );

				update_option( 'cdi_o_settings_section-collect-mod-horomon', sanitize_text_field( $row['horomon'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-horotue', sanitize_text_field( $row['horotue'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-horowed', sanitize_text_field( $row['horowed'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-horothu', sanitize_text_field( $row['horothu'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-horofri', sanitize_text_field( $row['horofri'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-horosat', sanitize_text_field( $row['horosat'] ) );
				update_option( 'cdi_o_settings_section-collect-mod-horosun', sanitize_text_field( $row['horosun'] ) );
			}
		}
		?>
		  </div>
		</div>
		<br class="clear">
		<em></em>
		<p></p>
	  </form>
	  <em></em>                            
	  <!-- ************************************************************************************************** -->
	  <div class="meta-box-sortables">
	  </div>
	</div>
	<!-- ************************************************************************************************** -->
  </div>
  <br class="clear">
</div>
		<?php
	}

}
?>
