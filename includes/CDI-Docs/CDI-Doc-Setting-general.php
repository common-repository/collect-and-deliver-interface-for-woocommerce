<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI - General settings', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";

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

	 // ******************************* Pilotage de CDI+
	 $html .= '<p> </p>';
	 $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	 $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Pilotage de CDI+</p>";
	 $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
	   $html .= "<p style='color:black;'>Le pilotage de CDI se fait à 4 endroits :</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>Pour vos réglages :</p>";
		   $html .= "<p style='color:black; margin-left:4em;'>- Dans ces pages des réglages où plusieurs onglets vous permettent de définir vos paramètres d’exploitation. La plupart des paramètres sont prédéfinis pour vous guider et éviter une première mise en route fastidieuse.</p>";
		   $html .= "<p style='color:black; margin-left:4em;'>- Dans l’onglet « Expédition » des réglages Woocommerce (dans la barre menu horizontale de Woocommerce), où l’ajout d’une nouvelle instance de la méthode de livraison CDI dans vos shipping zones, vous permet de définir globalement les conditions et tarifs d’expédition applicable à vos clients.</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>Lors de votre exploitation :</p>";
		   $html .= "<p style='color:black; margin-left:4em;'>- Dans chaque commande Woocomerce, qui une fois ouverte sur son détail, laisse apparaître une « Métabox CDI » (en bas à droite dans la commande) résumant sa situation courante d’exploitation.</p>";
		   $html .= "<p style='color:black; margin-left:4em;'>- Dans la « passerelle CDI » (dans la liste menu verticale de Woocommerce sur la gauche de l’écran), qui  donne les outils de travail et la situation d’exploitation des colis en cours.</p>";
	 $html .= '</div></div>';


	 // ******************************* Organisation des réglages CDI
	 $html .= '<p> </p>';
	 $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	 $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Organisation de la page des réglages CDI</p>";
	 $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
	   $html .= "<p style='color:black;'>Cette présente page des réglages CDI comprend :</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>une liste d’onglets CDI spécialisés pour le paramétrage de certaines taches : Réglages Généraux, Interface Client, Expéditions, Impression Adresses, Références, et CN23. Elle est suivi d’onglets permettant le paramétrage du fonctionnement de chacun des transporteurs : Colissimo, Mondial Relay, UPS, Collect, …</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>une barre horizontale de bouton de commande :</p>";
		   $html .= "<p style='color:black; margin-left:4em;'>- « Réinstaller les paramètres officiels de CDI » : ce sont des paramètres en rouge, généralement attachés au périmètre de fonctionnement des transporteurs. Ils sont stables mais sujets toutefois à d’éventuellement évolutions chez les transporteurs.</p>";
		   $html .= "<p style='color:black; margin-left:4em;'>- « Réinstaller la passerelle CDI » : son utilité est en cas d’urgence quand une commande WC non au format adéquat ou altérée par un plugin, bloque le bon fonctionnement de la Passerelle CDI.</p>";
	 $html .= '</div></div>';

	 // ******************************* Descriptif
	 $html .= '<p> </p>';
	 $html .= "<p style='color:black;'>« Critères par défaut » :</p>";
	 $html .= "<p style='color:black; margin-left:2em;'>Les grands critères par défaut pour les colis sont donnés dans les premiers champs : Type de colis, Poids de la tare (carton vide), Livraison avec signature, Assurance additionnelle, Type de retour choisi sur non délivrance pour certains pays, Nombre de jours pour la possibilité donnée au destinataire de faire un retour de colis. Du fait des modes de fonctionnement différents selon les transporteurs, ces données pourront être interprétées différemment selon leurs particularités.</p>";
	 $html .= "<p style='color:black;'>« Fonctionnalités Plugin » :</p>";
	 $html .= "<p style='color:black; margin-left:2em;'>La zone « Fonctionnalités Plugin » pilote les options techniques du plugin : Suppression des paramètres CDI lorsque le plugin est désinstallé définitivement (à utiliser avec une grande prudence), Mise en mode debug de wordpress pour tracer les messages de CDI, Définir les rôles Wordpress ayant accès à la passerelle CDI, Fixer une limite de taille aux documents logistiques à produire.</p>";
	 $html .= "<p style='color:black;'>« Références dans les colis » :</p>";
	 $html .= "<p style='color:black; margin-left:2em;'>La zone « Références dans les colis » précise le choix de la référence à appliquer aux étiquettes de colis.</p>";
	 $html .= "<p style='color:black;'>« Enchaînements automatisés » :</p>";
	 $html .= "<p style='color:black; margin-left:2em;'>La zone « Enchaînements automatisés » indique les options d’automatisme à actionner : Insérer automatiquement un colis dans la passerelle pour chaque commande, Nettoyage automatique de la  Passerelle CDI de ses colis traités, Passage automatique des commandes traitées dans le statut « Terminé ».</p>";
	 $html .= "<p style='color:black;'>« Adresse e-marchand » :</p>";
	 $html .= "<p style='color:black; margin-left:2em;'>La zone « Adresse e-marchand » indique les coordonnées du e-marchand qui seront communiquées aux transporteurs. Chacun utilisera tout ou partie de ces données lorsque ses services seront activés.</p>";

   // Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


