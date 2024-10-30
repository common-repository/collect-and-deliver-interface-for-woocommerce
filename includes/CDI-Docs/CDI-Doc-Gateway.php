<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI - Shipping Gateway', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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

	 $html .= '<p> </p>';
	 $html .= '<strong> ';
	 $html .= "<p style='color:black;'>La Passerelle de livraison CDI se veut en quelque sorte le quai de chargement des colis sortant de Woocommerce. On y gère des colis à raison d’un colis par commande WC en situation classique.</p>";
	 $html .= "<p style='color:black;'>Les colis en Passerelle n’ont pas de données propres à eux. Toutes leurs données sont des recopies des données de commande . Autrement dit, un colis en Passerelle pourra être détruit/supprimer sans conséquences dès lors que ses quelques données propres sont déjà reprises dans la commande WC.</p>";
	 $html .= "<p style='color:black;'>ATTENTION. Des déplacements de fonctions ont lieu par rapport à la version historique «Colissimo Delivery Integration». Par exemple, les impressions de masse des étiquettes d'affranchissement et des CN23 se trouvent maintenant dans les «Documents Logistiques».</p>";

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Pilotage de votre exploitation</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
	   $html .= "<p style='color:black;'>L’exploitation des colis se fait à 2 endroits :</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>- Dans la « passerelle CDI » (la présente page), qui  donne pour les colis en cours, les outils de travail et les situations d’exploitation.</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>- Dans la « Métabox CDI » de chaque commande WC, une fois ouverte sur son détail (en bas à droite dans le détail de la commande). Elle résume la situation courante d’exploitation du colis attaché à la commande. On peut y modifier des caractéristiques de la commande/colis qui seront alors prises en compte lorsque le colis sera soumis à son transporteur.</p>";
	   $html .= "<p style='color:black;'>La création d’un colis en Passerelle CDI se fait depuis la page liste des commandes WC (ou accessoirement depuis la Métabox CDI de la commande) :</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>- Une icône représentant un colis ouvert indique qu’aucun colis n’existe en Passerelle CDI pour cette commande ;</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>- Un clic et l’icône passe à un diable chargé de colis indiquant qu’un colis existe dans la Passerelle CDI, mais n’est pas encore traité par son transporteur ;</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>- Lorsque l’icône devient un camion, cela signifie que le colis a été soumis avec succès à son transporteur dans la Passerelle CDI, et que les informations de suivi sont désormais reportées dans la commande. Le colis en Passerelle CDI a alors été supprimé automatiquement si vous aviez choisi cette option dans les réglages CDI.</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>C’est ensuite un clic sur l’icône «Terminer» de WC qui ferme le traitement colis de CDI. WC envoie son mail de terminaison de commande au client internaute, lequel inclura alors les informations CDI de suivi du colis chez le transporteur.</p>";
		 $html .= "<p style='color:black; margin-left:2em;'>Évidemment, ce sont vos choix dans les réglages CDI « Enchaînements automatisés » qui vous permettront d’atteindre l’optimum selon votre situation d’exploitation.</p>";
	   $html .= '</div></div>';

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Organisation de la Passerelle CDI</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
		 $html .= "<p style='color:black;'>Elle comprend :</p>";
		   $html .= "<p style='color:black; margin-left:2em;'>Une barre horizontale de bouton de commande :</p>";
			 $html .= "<p style='color:black; margin-left:4em;'>- « Documents logistiques » : il renvoie vers les utilitaires de production des documents logistiques (voir ci-dessous). Au clic sur le bouton pour vous renvoyer vers les utilitaires, il passe en rouge.</p>";
			 $html .= "<p style='color:black; margin-left:4em;'>- « Lancer debug» : Quand la fonction debug a été autorisée dans les réglages généraux de CDI, les messages de debug de CDI sont produits pour tous ses modules et sont routés vers la destination par défaut de Wordpress (dépend de l’hébergeur et de vos choix d’installation).  L’utilisation du bouton « Lancer debug » permet de vous donner une gestion simplifiée en routant les messages vers le fichier « /wp-content/debug.log » de votre installation et en vous ouvrant l’accès à l’utilitaire d’exploitation du debug CDI (voir ci-dessous).</p>";
		   $html .= "<p style='color:black; margin-left:2em;'>Un tableau des colis, la Passerelle CDI elle-même (au centre).</p>";
		   $html .= "<p style='color:black; margin-left:2em;'>Les actions globales sur les colis de la Passerelle CDI (à droite).</p>";
	   $html .= '</div></div>';

	 $html .= "<p style='color:black;'>« Tableau des colis en Passerelle CDI : »</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le tableau indique les colis présents avec leurs caractéristiques essentielles et leur situation de traitement : colis, commande, destination, destinataire, code de suivi, étiquette d’affranchissement, cn23, transporteur.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Un colis comportant un code de suivi est considéré déjà traité, et ne sera pas retraité une deuxième fois.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>En dessous de ce tableau, des boutons permettent d’en sélectionner les lignes pour les gérer (Bloquer, Libérer, Supprimer) et un bouton Enregistrer pour valider les modification apportée manuellement (ou par une douchette) aux codes de suivi. Les boutons Bloquer/Libérer permettent d’isoler des colis qui restent en attente d’un traitement externes (articles en attente, incident sur destinataire, pb avec transporteur, etc …) ; ils ne seront donc pas pris en compte par les traitement de CDI et les soumissions aux transporteurs tant qu’ils seront dans l’état Bloqué.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Les colis en Passerelle n’ont pas de données propres à eux. Toutes leurs données sont des recopies des données de commande . Autrement dit, un colis en Passerelle CDI pourra être détruit/supprimer sans conséquence dès lors que ses  données sont déjà reprises dans la commande WC. C’est pourquoi, selon votre organisation de travail, il sera toujours possible de réinstaller une Passerelle CDI.</p>";

	 $html .= "<p style='color:black;'>« Actions globales sur les colis de la Passerelle : »</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Ce sont les boutons en colonne de droite. Ils traitent tous les colis présents en Passerelle, hormis les colis bloqués et ceux ne concernant pas un transporteur. On y trouve notamment les boutons permettant de solliciter les demandes d’affranchissement chez les transporteurs, ainsi que des utilitaires portant sur les colis en Passerelle CDI :</p>";
		 $html .= "<p style='color:black; margin-left:4em;'>- Les demandes d’étiquettes d’affranchissement par les transporteurs. Code de suivi, Étiquette d’affranchissement, Cn23 (s’il y a lieu) seront remplis automatiquement par le transporteur concerné ;</p>";
		 $html .= "<p style='color:black; margin-left:4em;'>- « Manuel » qui vous donne un tableur (CSV) des caractéristiques des colis encore à traiter selon votre procédure propre ;</p>";
		 $html .= "<p style='color:black; margin-left:4em;'>- « Personnalisé » qui active un filtre Wordpress donnant la main à votre programme, pour faire vous-mèmes vos traitements avec vos transporteurs non  pris en charge par CDI ;</p>";
		 $html .= "<p style='color:black; margin-left:4em;'>- « Impression Adresses » qui vous permet l’impression d’étiquettes adresse pour les cas simplifiés (courrier, lettres suivies, etc …).</p>";

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Debug CDI</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
		 $html .= "<p style='color:black;'>La page de debug CDI s’ouvre par le bouton en haut de passerelle. Le bouton passe en rouge et le debug ne doit pas être fermé si on souhaite un enregistrement en continu pendant l’exploitation du site.</p>";
		 $html .= "<p style='color:black;'>Les traces de debug s'appliquent à tous les modules CDI. Elles sont stockées dans le fichier wp-content/cdilog.log et ne contient que les messages concernant CDI. L’utilisation de l’outil de Debug CDI est important en exploitation courante, pour analyser le contenu des messages après  un rejet d’un transporteur sur un colis.</p>";
		 $html .= "<p style='color:black;'>Par ailleurs, CDI peut visualiser les fichiers log :  error_log et wp-content/debug.log lorsqu'ils existent. Pour activer le fichier wp-content/debug.log , le paramétrage de config.php doit autoriser le mode debug avec:  define( 'WP_DEBUG', true ); define( 'WP_DEBUG_LOG', true ); define( 'WP_DEBUG_DISPLAY', false ); . Ces 2 fichiers log ne sont pas purgeables par CDI, seul le log de CDI wp-content/cdilog.log l'est.</p>";
		 $html .= "<p style='color:black;'>Pour analyser les logs, vous pouvez éditer directement les fichiers concernés ou utiliser CDI  qui inclut une fonction de visualisation et de sélection des 3 fichiers de debug : wp-content/cdilog.log, wp-content/debug.log et error_log.</p>";
		 $html .= "<p style='color:black;'>Avertissement : Dans une exploitation stabilisée, sauf à avoir pris des dispositions ad-hoc pour cela, il est préférable de ne pas rester dans un mode 'debug' permanent du fait des difficultés pouvant se poser pour les logs (puissance, encombrement, sécurité). Concernant CDI, son log cdilog.log peut être facilement et très rapidement réactivé (voir réglages généraux CDI) quand il faut traiter un problème d'exploitation particulier nécessitant une analyse de son log.</p>";
	   $html .= '</div></div>';
	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Documents logistiques</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
		 $html .= "<p style='color:black;'>Cette page s’ouvre par le bouton en haut de passerelle.</p>";
		 $html .= "<p style='color:black;'>Le dispositif de sélection multiple permet de choisir les colis ou commandes de la Passerelle ou de l’ensemble du site. La sélection d’un transporteur à ce niveau permet de créer des documents ne concernant que ce transporteur. </p>";
		 $html .= "<p style='color:black;'>Les opérations se produisent toujours en 3 temps : 1) on sélectionne les commandes/colis que l’on veux voir traités en documents logistiques ; 2) on choisit le transporteur, et le type de document par un clic dessus ; 3) le/les documents produits sont alors en colonne de droite, et un clic dessus permet de les ouvrir. </p>";
		 $html .= "<p style='color:black;'>3 types de documents sont essentiels selon l’organisation de votre exploitation :</p>";
		   $html .= "<p style='color:black; margin-left:2em;'>- Le bon de transport (BT) qui résume la liste des colis (avec leurs caractéristiques externes) que vous confiez à vos prestataires ou à vos transporteurs ;</p>";
		   $html .= "<p style='color:black; margin-left:2em;'>- Les listes de préparation (LP) qui vous détaille le contenu de chaque colis (avec ses différents articles) que vous confiez ensuite à vos préparateurs ;</p>";
		   $html .= "<p style='color:black; margin-left:2em;'>- L’état des livraison (EL) qui résume la liste de vos colis avec leur situation d’acheminement chez votre transporteur.</p>";
		 $html .= "<p style='color:black;'>D’autres outils globaux peuvent vous simplifier l’exploitation : impressions globales de toutes les étiquettes d’affranchissement et Cn23 déjà produites dans la Passerelle CDI (concerne les transporteurs donnant un pdf), ou productions d’historiques.</p>";
	   $html .= '</div></div>';

   // Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


