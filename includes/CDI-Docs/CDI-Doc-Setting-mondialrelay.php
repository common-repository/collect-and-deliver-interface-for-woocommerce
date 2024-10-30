<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI - Mondial Relay settings', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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
	 $html .= "<p style='color:black;'>Cette page appliquent des réglages concernant le transporteur Mondial Relay.</p>";
	 $html .= "<p style='color:black;'>Mondial Relay fonctionne en Web services, et pour cela nécessite que soit souscrit par le gestionnaire du site marchand un contrat avec lui.  </p>";
	 $html .= "<p style='color:black;'>«Contrat (Enseigne) Mondial Relay » : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Dans cette zone doivent figurer le numéro de contrat (aussi appelé Enseigne) et le mot de passe communiqués par Mondial Relay.</p>";
	 $html .= "<p style='color:black;'>« Disposition étiquettes » : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>C’est le format PDF dans lequel seront converties les étiquettes d’affranchissement. PDF A4 et PDF 10x15 sont en présentation portrait ; PDF A5 en présentation paysage.</p>";
	 $html .= "<p style='color:black;'>« Suivi colis » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Cette zone sert à indiquer le texte et l’url permettant d’interroger un suivi de colis chez Mondial Relay.</p>";
	 $html .= "<p style='color:black;'>« Service des Retours Mondial Relay» :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Cette zone précise les dispositions applicables pour les retours de colis par le destinataire. La fonction est globalement débrayable pour les sites ne pratiquant pas de retour par Mondial relay.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>La fonction étant opérée par le client internaute souhaitant retourner un colis, plusieurs zones permettent de le guider pour produire et imprimer son étiquette d’affranchissement de retour.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Figure également dans cette zone le mode livraison des colis retour au e-marchand : livraison chez client chargeur, livraison en  point relais dont le numéro devra alors etre renseigné. Le standard est la livraison en point relay - 24R.</p>";
	 $html .= "<p style='color:black;'>« Autres paramètres » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Figure dans cette zone le mode standard de collecte des colis du e-marchand : collecte chez client chargeur, collecte par dépôt en point relais, collecte au domicile par 1 personne, collecte au domicile par 2 personnes (colis lourds ou encombrants). Le standard est la  collecte en Relay - REL.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>A noter que quand la destination est France, les livraisons à domicile ne sont pas acceptées, mais seules les livraisons en relais.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Ces paramètres rouges (à ne pas modifier sauf si changements chez Mondial Relay) concernent des dispositions techniques de fonctionnement du transporteur : pays pour lesquels Mondial Relay assure une livraison en relais, pays pour lesquels Mondial Relay assure une livraison à domicile.</p>";
	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Codes d’erreur Mondial Relay :</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
		 $html .= "<p style='color:black;'>
   1:Enseigne invalide
   2:Numéro d'enseigne vide ou inexistant
   3:Numéro de compte enseigne invalide
   5:Numéro de dossier enseigne invalide
   7:Numéro de client enseigne invalide(champ NCLIENT)
   8:Mot de passe ou hachage invalide
   9:Ville non reconnu ou non unique
   10:Type de collecte invalide
   11:Numéro de Relais de Collecte invalide
   12:Pays de Relais de collecte invalide
   13:Type de livraison invalide
   14:Numéro de Relais de livraison invalide
   15:Pays de Relais de livraison invalide
   20:Poids du colis invalide
   21:Taille (Longueur + Hauteur) du colis invalide
   22:Taille du Colis invalide
   24:Numéro d'expédition ou de suivi invalide
   26:Temps de montage invalide
   27:Mode de collecte ou de livraison invalide
   28:Mode de collecte invalide
   29:Mode de livraison invalide
   30:Adresse (L1) invalide
   31:Adresse (L2) invalide
   33:Adresse (L3) invalide
   34:Adresse (L4) invalide
   35:Ville invalide
   36:Code postal invalide
   37:Pays invalide
   38:Numéro de téléphone invalide
   39:Adresse e-mail invalide
   40:Paramètres manquants
   42:Montant CRT invalide
   43:Devise CRT invalide
   44:Valeur du colis invalide
   45:Devise de la valeur du colis invalide
   46:Plage de numéro d'expédition épuisée
   47:Nombre de colis invalide
   48:Multi-Colis Relais Interdit
   49:Action invalide
   60:Champ texte libre invalide (Ce code erreur n'est pas invalidant)
   61:Top avisage invalide
   62:Instruction de livraison invalide
   63:Assurance invalide
   64:Temps de montage invalide
   65:Top rendez-vous invalide
   66:Top reprise invalide
   67:Latitude invalide
   68:Longitude invalide
   69:Code Enseigne invalide
   70:Numéro de Point Relais invalide
   71:Nature de point de vente non valide
   74:Langue invalide
   78:Pays de Collecte invalide
   79:Pays de Livraison invalide
   80:Code tracing : Colis enregistré        
         </p>";
	   $html .= '</div></div>';

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Support des Web Services Mondial Relay</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
		 $html .= "<p style='color:black;'>Le support des Web Services Mondial Relay n'est pas sur le forum Wordpress, mais sur des accès direct au transporteur :</p>";
		 $html .= "<p style='color:black;'>Message en ligne sur Mondial Relay : https://www.mondialrelay.fr/nous-contacter/ . Ou mail à offrestart@mondialrelay.fr . </p>";
	   $html .= '</div></div>';


   // Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


