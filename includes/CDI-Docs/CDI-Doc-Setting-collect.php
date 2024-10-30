<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI - Click & Collect settings', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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
	 $html .= "<p style='color:black;'>Cette page appliquent les réglages concernant le transporteur Click & Collect.</p>";
	 $html .= "<p style='color:black;'>Il s’agit d’un transporteur interne à CDI qui assure un service de Click & Collect multipoints, avec affichage/sélection des points de retrait sur carte, impression d’un ticket de collecte, lien de suivi pour le client, et flashage d'un QR-Code pour les commandes délivrées (flashage au choix selon les organisations par un coursier, par le point de retrait, ou par le client).</p>";
	 $html .= "<p style='color:black;'>Ce service s’adresse aux regroupements de commerçants, aux enseignes multi-magasins, aux organisations de franchisés, et aux e-commerçants mono magasin ayant ou non des points de retrait additionnels, et à tous utilisant ou non un service de coursier pour assurer leur livraisons. </p>";
	 $html .= "<p style='color:black;'>«Réglages des points de retrait» :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>La bibliothèque des points des retrait peut être importée ou partager depuis un fichier de démarrage dans le répertoire /uploads de CDI. Les fonctions d'édition des points de retrait se fait depuis les réglages CDI après «Open» du point choisi (adresse, mail, téléphone, coordonnées GPS, horaires).</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Un fichier de démarrage de démonstration vous est proposé, si le tableau des points de retrait est vide.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>En cas de recherche de position GPS infructueuse, notamment si la position est indiquée en front sur l'équateur, il est préférable d'indiquer la position GPS effective du point de retrait, laquelle a la priorité.</p>";
	 $html .= "<p style='color:black;'>«Suivi des colis» : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Comme avec tous les transporteurs, le suivi des colis est propre au transporteur dès lors que le colis CDI est dans l'état 'Dans le camion', c'est à dire remis au transporteur. Les colis en Click&Collect ont 5 états de suivi successifs et optionnels. En priorités croissantes : «En préparation», «Disponible au point de retrait», «Pris en charge par coursier», «Livré au client», «Validation du client». Un choix de la situation de départ est proposé.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Lors d'un scan du QR-code, un code de sécurité est utilisable optionnellement. Cela dépend des organisations et si le scan est effectué par le client, le point de retrait ou un coursier. Pour le client, le code de sécurité est le prix total de la commande en cts d'euros. Pour le point de retrait ou pour un coursier, c'est un code spécifique (code de livraison, qui initialement est l'id du point de retrait) .
	   .</p>";	   
	   $html .= "<p style='color:black; margin-left:2em;'>La personnalisation du logo et des fonds d'écran visibles du client, est réalisable par des filtres CDI.</p>";

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Support de CDI Click & Collect</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
		 $html .= "<p style='color:black;'>Le support de CDI Click & Collect se fait sur le forum Wordpress ; https://wordpress.org/support/plugin/collect-and-deliver-interface-for-woocommerce/ . </p>";
	   $html .= '</div></div>';

   // Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


