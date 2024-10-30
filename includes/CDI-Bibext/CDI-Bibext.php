<?php
/*
 * Plugin Name: CDI - Collect and Deliver Interface
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('ABSPATH')) exit;
/****************************************************************************************/
/* Bibext                                                                               */
/****************************************************************************************/

class cdi_c_Bibext {
  public static function init() {
    if (class_exists ('SoapClient')) {
      require_once dirname(__FILE__) . '/nusoap/nusoap.php';
    }
    if (!class_exists('FPDF')) {
      require_once dirname(__FILE__) . '/FPDF/fpdf.php';
    }
    if (!class_exists('FPDI')) { 
      require_once dirname(__FILE__) . '/FPDI/fpdi.php';       
    }
    require_once dirname(__FILE__) . '/qr-code-master/vendor/autoload.php';   
  } 
}
?>
