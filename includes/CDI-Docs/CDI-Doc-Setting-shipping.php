<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI – Expéditions', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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

	 $html .= "<p style='color:black;'>Cette page de réglages appliquent des réglages communs à toutes les « méthodes de livraison CDI » de l’installation. Les paramètres spécifiques à chaque méthode se trouvent dans la méthode elle-même.</p>";
	 $html .= "<p style='color:black;'>«Activation/désactivation globale » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>L’activation ou la désactivation globale des méthodes de livraison CDI s’applique à toutes les méthodes CDI installées dans les zones d’expédition (shipping zones) de WC.</p>";
	 $html .= "<p style='color:black;'>« Ajout en front-end client d'une icône» :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Vues par le client, en avant de chaque ligne tarif des méthodes de livraison, des icônes standards sont proposée pour différencier les méthodes dont les « termid » sont du type « home », « pick » ou « shop ». Une personnalisation est possible par modification des icônes contenues dans le répertoire /images (mais elles sont réinitialisées à chaque nouvelle version). Des icônes spécifiques peuvent dynamiquement les remplacer en activant un filtre sur la méthode de livraison CDI.</p>";
	 $html .= "<p style='color:black;'>« Lots d’expédition» :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Divers dispositifs (par exemple les plugins de place de marché, ou d’abonnement) peuvent mettre en œuvre le mécanisme WC de multiples lots d’expédition « WC shipping packages ». Sur la suite du process, CDI ne traitera initialement qu’un seul de ces lots, qu’il convient d’indiquer ici. Le standard est d’indiquer le premier lot.</p>";
	 $html .= "<p style='color:black;'>«Liste étendue des TermId »: </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Les « termid » sont des terminaisons sur le nom des méthodes de livraison CDI, qui permettent de les distinguer sans erreur pour les désigner. Ces « termid » sont utilisés dans chaque méthode de livraison CDI, au niveau de chaque ligne tarif, et en standard un choix est proposé : home1 à home6, pick1 à pick6, shop1 à shop6. L’extension permet d’étendre cette liste et de la personnaliser.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>La valeur d’une ligne tarif produite par une méthode de livraison CDI a la structure suivante :
cdi_shipping_«nom transporteur»_«termid»:«x»:«z» , x et z étant des indices identifiants respectivement l’instance de la méthode CDI et la ligne tarif sélectionnée dans la méthode. Ex :
cdi_shipping_colissimo_home1:7:2 ; cdi_shipping_mondialrelay_pick1:8:1 ; cdi_shipping_ups_pick2:9:2</p>";


	// Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


