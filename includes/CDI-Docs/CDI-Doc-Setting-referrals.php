<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI - Références Livraisons', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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

	 $html .= "<p style='color:black;'>Les références aux méthodes de livraison permettent de leur attribuer diverses qualifications quand elles sont présentées au client internaute. Elles portent sur l’ensemble des méthodes de livraison CDI ainsi que les méthodes non CDI.</p>";
	 $html .= "<p style='color:black;'>La désactivation des références aux méthodes de livraison est globale pour l’ensemble des méthodes.</p>";

	 $html .= "<p style='color:black;'>«Points de retrait» :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>On définit dans la liste des méthodes de livraison  celles qui sont à traiter comme des points relai. C’est une suite de nom de tarif de méthode de livraison séparés par une virgule. Par exemple : cdi_shipping_colissimo_pick1=1, cdi_shipping_colissimo_pick2=0, cdi_shipping_mondialrelay_pick1, cdi_shipping_ups_pick1</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le qualificatif « =n » en fin du nom est optionnel. Il permet de préciser un type de point relai. Il n’est utile que pour certains transporteurs. Éventuellement, la désignation des méthodes de livraison à considérer peuvent comprendre leurs indices identifiant  l’instance de la méthode et la ligne tarif sélectionnée.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Pour rappel, la valeur d’une ligne tarif produite par une méthode de livraison CDI a la structure suivante :
cdi_shipping_«nom transporteur»_«termid»:«x»:«z» , x et z étant des indices identifiant respectivement l’instance de la méthode et la ligne tarif sélectionnée dans la méthode. Ex :
cdi_shipping_colissimo_home1:7:2 ; cdi_shipping_mondialrelay_pick1:8:1 ; cdi_shipping_ups_pick2:9:2</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Des choix de sélection permettent : a) de ne pas  afficher les lignes tarifs des points relai en cas d’incident en connexion externe, b) une ouverture automatique de la carte des points relai, c) la sélection d’un point relai par clic sur la carte. </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Il convient également de choisir le positionnement de la carte dans la page checkout de WC, et le type de carte : Google maps ou Open map (Open map  est le choix par défaut).</p>";

	 $html .= "<p style='color:black;'>«Codes produit à forcer » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Il est possible, dès la sélection de la méthode de livraison, de choisir un des « code produit » du transporteur concerné. Chaque transporteur utilise des codes produits différents. Ce choix aura priorité sur les algorithmes par défaut qu’appliquerait CDI. Ex : cdi_shipping_colissimo_home1=DOM, cdi_shipping_colissimo_home2=DOS,  cdi_shipping_mondialrelay_pick1=24L</p>";

	 $html .= "<p style='color:black;'>« Téléphone obligatoire : »</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Un numéro de téléphone étant obligatoire pour certains transporteurs et/ou dans certaines situations, il est possible de forcer ce contrôle de présence. Ex : cdi_shipping_colissimo_pick1, cdi_shipping_colissimo_pick2, cdi_shipping_mondialrelay_pick1, cdi_shipping_ups_pick1</p>";

	 $html .= "<p style='color:black;'>« Méthodes de livraison exclusives : »</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Ce sont des tarifs de méthodes de livraison que l’on souhaite voir présentés seuls au client internaute. Le cas typique est lorsqu’une gratuité est accordée, il n’y a pas de sens à présenter d’autres choix au client.</p>";

	// Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


