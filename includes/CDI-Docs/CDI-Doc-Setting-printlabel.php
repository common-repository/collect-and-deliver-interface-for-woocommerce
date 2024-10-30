<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI – Impression d’adresses', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
   $html .= '<strong>';

   // Début du Doc
/*
	 // ******************************* Exemple en niveau 1
	 $html .= '<p> </p>' ;
	 $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	 $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>xxxxxx</p>" ;
	 $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
	   $html .= "<p style='color:black;'>xxxxxx</p>" ;

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>xxxxxx</p>" ;
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
	   $html .= "<p style='color:black;'>xxxxxx</p>" ;
	   $html .= "<p style='color:black;'>xxxxxx</p>" ;
	   $html .= "<p style='color:black;'>xxxxxx</p>" ;
	   $html .= '</div></div>' ;

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>xxxxxx</p>" ;
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
	   $html .= "<p style='color:black;'>xxxxxx</p>" ;
	   $html .= "<p style='color:black;'>xxxxxx</p>" ;
	   $html .= "<p style='color:black;'>xxxxxx</p>" ;
	   $html .= '</div></div>' ;

	 $html .= '</div></div>' ;
*/

	 // ******************************* Descriptif
	 $html .= '<p> </p>';

	 $html .= "<p style='color:black;'>Cette page de réglages définit les paramètres applicables à l’utilitaire de production d’étiquettes d’adresses de livraison. L’utilitaire s’applique sur les commandes ayant un colis en Passerelle CDI. Il ne doit pas être confondu avec les process de production d’étiquettes d’affranchissement de chacun des transporteurs.</p>";
	 $html .= "<p style='color:black;'>L’utilitaire permet d’utiliser des feuilles d’étiquettes autocollantes. C’est un usage typique pour les lettres suivies.</p>";

	 $html .= "<p style='color:black;'>«Disposition des étiquettes dans la feuille » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Ici sont définis la taille de la feuille, le nombre d’étiquettes dans la feuille, la zone d’impression dans chaque étiquette.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>La position de la 1ère étiquette à imprimer et l’indicateur « Mode de gestion de la position de la 1ère étiquette » permet de choisir un mode d’impression, pour notamment utiliser le reste d’une planche d’étiquette déjà entamée.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le mode « grille de test » est une aide au cadrage de l’imprimante. </p>";

	 $html .= "<p style='color:black;'>«Mise en page et mise en forme dans la feuille » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>En codification CSS, il est possible de préciser le positionnement dans la feuille, ainsi que la mise en forme de l’impression de chaque étiquette.</p>";


	// Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


