<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI - UPS settings', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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
	 $html .= "<p style='color:black;'>Cette page appliquent des réglages concernant le transporteur UPS.</p>";
	 $html .= "<p style='color:black;'>UPS fonctionne avec des API de services, et pour cela nécessite que soit souscrit par le gestionnaire du site marchand un contrat avec lui. </p>";
	 $html .= "<p style='color:black;'>«Vos identifiants d'accès UPS » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Dans cette zone doivent figurer les 4 éléments permettant à UPS de vous authentifier correctement. A savoir, 1) votre Identifiant d’utilisateur,   2) votre mot de passe, 3) une clé d’UPS vous autorisant accès (Accès License UPS), et 4) votre numéro de compte UPS.</p>";
	 $html .= "<p style='color:black;'>« Production » : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>UPS propose un mode de test pour faciliter les intégrations.  Dès lors que la case production est cochée, UPS procèdera à la facturation effective pour les services sollicités.</p>";
	 $html .= "<p style='color:black;'>« Disposition étiquettes » : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>C’est le format PDF dans lequel seront converties les étiquettes d’affranchissement. PDF A4 et PDF 10x15 sont en présentation portrait ; PDF A5 en présentation paysage.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Les CN23 sont systématiquement établis en format A4 portrait  (UPS ne proposant que des CN22 pour petits colis, et en unités US, c’est CDI qui établit les CN23 selon le modèle des pouvoirs publics).</p>";
	 $html .= "<p style='color:black;'>« Suivi colis » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Cette zone sert à indiquer le texte et l’url permettant d’interroger un suivi de colis chez UPS.</p>";
	 $html .= "<p style='color:black;'>« Service des Retours colis » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Cette zone précise les dispositions applicables pour les retours de colis par le destinataire. La fonction est globalement débrayable pour les sites ne pratiquant pas de retour par UPS.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>La fonction étant opérée par le client internaute souhaitant retourner un colis, plusieurs zones permettent de le guider pour produire et imprimer son étiquette d’affranchissement de retour.</p>";
	 $html .= "<p style='color:black;'>« Autres paramètres » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le 'Code Service' UPS permet de déterminer un service par défaut lorsque la Métabox CDI n’en contient pas et n’est pas un Access Point UPS.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Les demandes d’affranchissement UPS fonctionnent en 2 requètes successives : une première qui obtient une évaluation du cout applicable, et une deuxième qui est l’acceptation de ce cout. Une cotation maximale, modifiable, peut etre indiquée dans la Métabox CDI ; par défaut c’est un pourcentage du montant de la commande qui initialise la Métabox CDI. Ce mécanisme de controle des couts ne s'applique que pour les étiquettes Aller et non pour les étiquettes de Retour produites pas le client internaute.</p>";

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Support des Web Services UPS</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
		 $html .= "<p style='color:black;'>Le support des Web Services UPS n'est pas sur le forum Wordpress, mais sur des accès direct au transporteur :</p>";
		 $html .= "<p style='color:black;'>Message en ligne sur UPS : https://www.ups.com/fr/fr/help-center/technology-support.page? . </p>";
	   $html .= '</div></div>';

   // Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


