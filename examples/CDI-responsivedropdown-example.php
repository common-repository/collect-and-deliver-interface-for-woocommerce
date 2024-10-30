<?php

/**
 * This file is part of the CDI - Collect and Deliver Interface plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('ABSPATH')) exit;

//
// *** Example to have a more responsive pickup location drop-down list
//
// Wp_is_mobile() is a simplified wordpress function that considers tablets to be mobile.
// For a finer setting, we advise you to use the many functions of Mobble plugin (https://wordpress.org/plugins/mobble/)
//

  add_filter( 'cdi_filterhtml_retrait_selectoptions','example_cdi_filterhtml_retrait_selectoptions', 10, 2) ; 
  function example_cdi_filterhtml_retrait_selectoptions ($insertselect, $listePointRetraitAcheminement) {
    if (function_exists('is_mobile')) { // Mobble plugin seems active
      $ismobile = is_mobile() ;
    }else{ // Mobble plugin not active, so take the wp function
      $ismobile = wp_is_mobile() ;
    }
    if ($ismobile){ // Mobile
      $newhtml = '<select id="pickupselect" name="pickupselect">' . '<option value="">Choisir votre point de retrait</option>' ;
      foreach ($listePointRetraitAcheminement as $PointRetrait) {
        $newhtml .= '<option value=' . $PointRetrait->identifiant . '>' . $PointRetrait->nom . ' à ' . $PointRetrait->distanceEnMetre . 'm' . '</option>' ;
      }
      $newhtml .= '</select>' ;
    }else{
      $newhtml = $insertselect ; // No change for Desktop
    }
    return $newhtml; 
  }

?>
