<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI – Interface Client', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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

	 $html .= "<p style='color:black;'>Cette page de réglages appliquent les réglages relatif à l’interface avec le client internaute. Certains autres réglages plus spécifiques aux transporteurs sont dans leurs réglages respectifs.</p>";
	 $html .= "<p style='color:black;'>«Code de suivi pour le client » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le cheminement du colis du client internaute lui est indiqué dans chaque mail envoyé, et/ou dans les vues WC lui donnant le détail de sa commande. Il est également possible de préciser la position de ces informations dans les mails.</p>";
	 $html .= "<p style='color:black;'>« Armées (S1) » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>L’adressage de colis aux militaires français se fait via une procédure spéciale (Hub Armée) qui fait l’objet d’une sorte de pseudo code postal non connu de WC.</p>";
	 $html .= "<p style='color:black;'>« Extension des adresses Woocommerce aux normes postales » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Les adresses de WC sont composées de 2 lignes d’adressage sans précision particulière de leur usage, alors que les normes Afnor (NF Z 10-011) européenne (NE 14-142) et internationale (S42) prévoient 4 lignes à usage défini. Le choix de cette option étend ainsi les 2 lignes d’adressage de WC.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Les transporteurs ont cependant adopté ces normes à différents stades, et donc avec différentes longueurs des lignes. Le réglage correspondant de CDI permet de s'ajuster au mieux, 32 caractères étant le meilleur choix pour obtenir le maximum d’interchangeabilité entre les transporteurs.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>La validité d’une adresse de livraison WC en France, peut être vérifiée dans la Passerelle CDI via une application de La Poste. Pour cela il convient de s’enregistrer sur le site de La Poste et de se faire attribuer une clé d’identification.</p>";
	 $html .= "<p style='color:black;'>«Ajout adresse de retrait (point relay) dans les commandes et mails » : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le principe de CDI est de ne jamais modifier dans l’adresse dite de livraison de WC. C’est une adresse WC à la main du client internaute, dont le but est de définir le bénéficiaire final du colis. Ce qui se passe bien pour des envois à domicile, peut être réducteur pour les envois du type dépôt magasin, consigne ou point relay, et une adresse intermédiaire du retrait peut être nécessaire dans ces cas.</p>";
	 $html .= "<p style='color:black;'>«Gestion des étiquettes retour» : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le principe de CDI est de favoriser la production des étiquettes retour par le client internaute lui-même, de sorte à alléger l'exploitation du site. Le client a à sa disposition, dans sa vue de commande, la possibilité de générer et imprimer une étiquette retour dans le cadre de limites fixées par l'administrateur. L'administrateur a également cette possibilité de générer lui-même une étiquette retour, ainsi qu'un éventuel bordereau CN23.</p>";

	// Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';


   echo wp_kses_post( $html );


