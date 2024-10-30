<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI - Colissimo settings', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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
	 $html .= "<p style='color:black;'>Cette page appliquent des réglages concernant le transporteur Colissimo. </p>";
	 $html .= "<p style='color:black;'>Colissimo fonctionne en Web services, et pour cela nécessite que soit souscrit par le gestionnaire du site marchand un contrat avec La Poste-Colissimo. Ce doit être un contrat Entreprise incluant les 3 web services : Affranchissement, Choix de livraison, et Suivi colis.  </p>";
	 $html .= "<p style='color:black;'>« Contrat Colissimo » : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Dans cette zone doivent figurer le numéro de contrat et le mot de passe communiqués par le commercial de Colissimo.</p>";
	 $html .= "<p style='color:black;'>« Disposition étiquette » : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Cette zone précise les dispositions pour les étiquettes d’affranchissement que produira Colissimo : format de feuille, et cadrage, inclusion ou non des documents douaniers quand ils existent. CDI ne prend en charge que les étiquettes dans un format PDF.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Il faut également indiquer l’estimation du nombre de jours avant dépôt à La Poste.</p>";
	 $html .= "<p style='color:black;'>« Suivi colis » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Cette zone sert à indiquer le texte et l’url permettant d’interroger un suivi de colis chez Colissimo.</p>";
	 $html .= "<p style='color:black;'>« Service des Retours Colissimo » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Cette zone précise les dispositions applicables pour les retours de colis par le destinataire. La fonction est globalement débrayable pour les sites ne pratiquant pas de retour par Colissimo.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>La fonction étant opérée par le client internaute souhaitant retourner un colis, plusieurs zones permettent de le guider pour produire et imprimer son étiquette d’affranchissement de retour.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Les paramètres en rouge (à ne pas modifier sauf si changements chez Colissimo) sont les conditions actuellement applicables par Colisssimo en ce qui concerne : les entêtes des code suivis « aller » contrôlés par Colissimo, la liste des pays et codes produits applicables pour ces retours. </p>";
	 $html .= "<p style='color:black;'>« Zones géographiques »</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>L’offre commerciale de Colissimo est articulée en 4 zones géographiques (France, Outre-mer, Europe, International). Les paramètres rouges de ces zones indiquent pour chacune d’elles, les pays inclus, et les codes produits à utiliser par défaut pour les 3 catégories d’envoi : domicile sans signature, domicile avec signature, points relai.</p>";
	 $html .= "<p style='color:black;'>« Autres paramètres » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Ces paramètres rouges (à ne pas modifier sauf si changements chez Colissimo) concernent des dispositions techniques de fonctionnement du transporteur : substitution de codes produit,  pays pour lesquels sont possibles des envois sans signature, pays pour lesquels des «points de retrait» sont gérés, pays qui ne permettent pas, en cas de non livraison, un choix sur le mode de retour du colis.</p>";

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Codes d’erreur Colissimo :</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
		 $html .= "<p style='color:black;'> 
 Affranchissement :
 30000 Identifiant ou mot de passe incorrect
 30002 La date de dépôt est antérieure à la date courante
 30007 Client inactif. Veuillez prendre contact avec votre interlocuteur commercial. 
 30008 Service non autorisé pour cet identifiant. Veuillez prendre contact avec votre interlocuteur commercial afin de réinitialiser votre compte 
 30009 Service non autorisé pour ce produit. Veuillez prendre contact avec votre interlocuteur commercial
 30010 La date n'a pas été transmise
 30014 Le code produit n'a pas été transmis
 30015 Le code produit est incorrect
 30017 La valeur du champ contre remboursement est incorrecte
 30018 Le nom commercial n'a pas été transmis
 30020 Le montant total des frais de transport n'a pas été transmis
 30022 La langue de l'expéditeur est incorrecte
 30023 La langue du destinataire est incorrecte
 30025 Le type d'impression n'a pas été transmis
 30026 Le type d'impression est incorrect
 30065 Le nom de l'expéditeur n'a pas été transmis
 30043 Le prénom de l'expéditeur n'a pas été transmis
 30045 L'email de l'expéditeur n'a pas été transmis
 30046 L'email de l'expéditeur est incorrect
 30047 Le téléphone de l'expéditeur est incorrect
 30085 Le numéro de téléphone fixe de l'expéditeur est incorrect
 30089 La raison social du destinataire n'a pas été transmise
 30090 La taille du paramètre AddresseeParcelRef est nulle ou supérieure à 15
 30100 Le numéro / libellé de voie de l'expéditeur n'a pas été transmis
 30102 Le code pays de l'expéditeur n'a pas été transmis
 30103 Le code pays de l'expéditeur est incorrect
 30104 La ville de l'expéditeur n'a pas été transmise
 30106 Le code postal de l'expéditeur n'a pas été transmis
 30107 Le code postal de l'expéditeur est incorrect
 30108 Le code postal de l'expéditeur ne correspond pas au pays
 30109 Le code pays ou le code postal de l'expéditeur est incorrect pour le code produit fourni
 30200 Le nom du destinataire n'a pas été transmis
 30202 Le prénom du destinataire n'a pas été transmis
 30204 Le numéro / libellé de voie du destinataire n'a pas été transmis
 30206 Le code pays du destinataire n'a pas été transmis
 30207 Le code pays du destinataire est incorrect
 30208 La ville du destinataire n'a pas été transmise
 30210 Le code postal du destinataire n'a pas été transmis
 30211 Le code postal du destinataire est incorrect
 30212 Le code postal du destinataire ne correspond pas au pays
 30213 Le code pays ou le code postal du destinataire est incorrect pour le code produit fourni
 30220 Le numéro de portable du destinataire n'a pas été transmis
 30221 Le numéro de portable du destinataire est incorrect
 30222 Le courriel du destinataire n'a pas été transmis
 30223 Le courriel du destinataire est incorrect
 30300 Le poids du colis n'a pas été transmis
 30301 Le poids du colis est incorrect
 30303 La valeur du champ hors gabarit est incorrecte
 30306 L'option recommandation est incorrecte
 30309 L'option valeur assurée est incorrecte
 30310 Le niveau de recommandation n'a pas été transmis
 30311 Le niveau de recommandation est incorrect
 30312 Les options ne permettent pas d’effectuer un étiquetage
 30313 Le synonyme du code produit est vide
 30316 Le code pays ne permet pas d’effectuer un étiquetage
 30317 Les options ne permettent pas d’effectuer un étiquetage
 30318 Le partenaire  ne gère pas le code produit YYY  : partenaire retourné par le routing YYY : le code produit passé en entrée du WS
 30321 Le numéro de colis est incorrect
 30323 Le type de choix retour n'a pas été transmis
 30324 Le type de choix retour est incorrect
 30325 L'option avis de réception est incorrecte
 30326 L'option Franc de Taxes et de Droits est incorrecte
 30327 Le numéro de colis n'a pas été transmis
 30400 Le code point de retrait n'a pas été transmis
 30401 Le code point de retrait est incorrect
 30402 L’adresse point de retrait n'a pas été transmis
 30403 Le code ou l’adresse point de retrait n'a pas été transmis
 30500 Le contenu du colis n’a pas été transmis
 30503 La catégorie de l’envoi n’a pas été transmise
 30504 La catégorie de l’envoi est incorrecte
 30505 Les articles contenus n’ont pas été transmis
 30506 Le nombre d’articles est supérieur au maximum
 30507 Le poids total des articles est supérieur au poids du colis
 30510 La description d'un article n'a pas été transmise
 30511 La description d'un article est incorrecte
 30512 La quantité d'un article n'a pas été transmise
 30513 La quantité d'un article est incorrecte
 30514 Le poids d'un article n'a pas été transmis
 30515 Le poids d'un article est incorrect
 30516 La valeur d'un article n'a pas été transmise
 30517 La valeur d'un article est incorrecte
 30518 Le numéro tarifaire d'un article n'a pas été transmis
 30519 Le numéro tarifaire d'un article est incorrect
 30520 Le pays d'origine d'un article n'a pas été transmis
 30521 Le pays d'origine d'un article est incorrect
 30522 La Référence de l'article n'a pas été transmise
 30523 Le nombre max d’articles est dépassé (100 max)
 30524 La devise n'a pas été transmise
 30528 Le numéro de colis d'origine n’a pas été transmis
 30525 Le numéro de colis d’origine est incorrect
 30529 Le numéro de facture d'origine n’a pas été transmis
 30530 La date de la facture d'origine n’a pas été transmise
 30533 La date de facture d’origine doit être antérieure à la date du jour
 30531 Le nombre max de colis d’origine est dépassé ( 5 max )
 30532 Le numéro de facture est incorrect
 30534 L’identifiant du document est incorrect
 30535 La référence importateur est incorrecte
 30536 La valeur de marchandises est supérieure au seuil autorisé
 30537 La devise doit être identique pour l’ensemble des articles du colis
 30538 Le code pays doit être identique pour l’ensemble des articles du colis
 30539 Le commentaire est trop long
 30540 Le poids total des articles contenus dans votre envoi ne peut être supérieur au poids initialement déclaré pour le colis.
 30541 Un seul identifiant document doit être transmis
 30542 La catégorie de l'envoi est incorrecte
 30543 La Référence de l'article est incorrecte
 30544 La devise est incorrecte
 30545 Le code pays expéditeur ne permet pas d’effectuer un envoi retour
 30546 Identifiant de facture et colis original n’a pas été transmis
 30547 Identifiant de facture et colis original inconnu ou incorrect
 30548 Article non rattaché à un colis et une facture
 30549 L’identifiant du colis et facture existe déjà
 30550 Il existe des doublons dans la liste des colis origine déclarée
 30551 La référence importateur n’a pas été transmise
 30552 Commentaire n’a pas été transmis
 30553 La date de facturation doit être identique pour un même numéro de facture. Veuillez saisir une nouvelle date.
 30554 Au moins une déclaration de colis origine doit être transmise.
 30600 Le champ {0} contient un caractère {1} non valide. Veuillez saisir à nouveau ce champ. {0} : Nom du champ {1} : caractères interdits
 30700 Le produit demandé n’existe pas dans le compte client
 30701 La plage utilisée est incorrecte
 30702 Ce numéro de colis a déjà été attribué à un colis il y a moins de 13 mois
 30703 La présence ou l’absence d’indication de plage n’est pas conforme à la solution souscrite.
 30704 Le produit transmis ne permet pas d'effectuer un service retour depuis l'international.
 30705 Le pays transmis n'est pas habilité à proposer le service retour depuis l'international.
 30800 Veuillez activer le dépôt en boite à lettres dans votre Back Office
 30801 Colis inexistant Le colis n’a pas été annoncé auprès de La Poste
 30802 Ce colis a déjà été pris en charge Le colis a déjà été pris en charge par La Poste
 30803 Vous avez déjà pris rendez-vous Le colis a déjà fait l’objet d’une demande de d’emport en boite à lettres
 30804 Le produit retour n’est pas déposable en boite à lettre Le service de dépôt en boite à Lettres n’est pas disponible
 30805 Ce colis ne peut pas être déposé en boite à lettre L’adresse ne permet pas de faire une demande d’emport de colis déposé en boite à lettres
 30806 La date d’emport demandée est incorrecte La date d’emport demandée ne fait pas partie des prochaines date d’emport possibles à cette adresse
 30807 Le colis n’est pas autorisé à un dépôt en boîte aux lettres
 30808 Date emport invalide: vous n’avez pas activé le dépôt en boîte aux lettres dans votre Back Office 
 30809 Veuillez ne pas indiquer de date d’emport si vous avez choisi l’option : étiquette non déposable en boîte aux lettres 
 30810 Demande d’emport boîte aux lettres invalide : colis non déposable en boîte aux lettres 
 30811 La date d’emport demandée est incorrecte
 30812 Aucune date d’emport trouvée pour cette adresse L’adresse ne permet pas de faire une demande d’emport de colis déposé en boite à lettres 
 30813 La date d'emport n'a pas été transmise
 30814 Le nombre max de colis dépassé
 30815 Le nombre max de dates d'enlèvement dépassé
 30816 Impossible d'effectuer un dépôt en BAL avec les informations transmises. Un dépôt en BP est forcé
 30817 Le site de collecte n'a pas été transmis
 30818 Le site de collecte est incorrect
 30819 L’adresse n’est pas autorisée à une livraison dans la journée
 30820 Le service livraison en journée n’est pas possible à cette adresse.
 30900 Le nom du point retrait n'a pas été transmis
 30901 L’adresse du point retrait n'a pas été transmise
 30902 Le code postal du point retrait n'a pas été transmis
 30903 La ville du point retrait n'a pas été transmise
 30904 Le code pays du point retrait n'a pas été transmis
 40011 Erreur: code pays de destination du colis incorrect
 40012 Erreur: Pays non ouvert au service Retour Colissimo International ou incorrect. Contacter votre support client
 40013 Erreur: Relation pays expéditeur et pays de destination non ouverte ou incorrecte. Contacter votre support client 
 40014 Erreur: Plage de numéros de colis épuisée. Contacter votre support client 
 40015 Service momentanément indisponible
 40016 Problème de paramétrage de seuil. Le pays d’origine {0} n’existe pas 
 40017 Les informations transmises semblent incohérentes : impossible de réaliser un affranchissement. Merci de contacter le support client si le problème persiste. 
 40018 Service non disponible. Contacter votre support client.
 14040 Les options assurance et recommandation ne sont pas compatibles. Veuillez sélectionner une ou l'autre de ces options. 

 Choix de livraison : 
 101 Numéro de compte absent
 102 Mot de passe absent
 104 Code postal absent
 105 Ville absente
 106 Date estimée de l’envoi absente
 107 Identifiant point de retrait absent
 117 Code ISO pays manquant
 120 Poids n’est pas un entier
 121 Poids n’est pas compris entre 1 et 99999
 122 Date n’est pas au format JJ/MM/AAAA
 123 Filtre relais n’est pas 0 ou 1
 124 Identifiant point de retrait incorrect
 125 Code postal incorrect (non compris entre 01XXX et 95XXX ou 980XX)
 127 RequestId incorrect
 129 Adresse incorrecte
 143 Code postal incorrect (format XXXX non respecté)
 201 Identifiant / mot de passe invalide
 144 Code postal incorrect, format non respecté
 145 Code postal obligatoire
 146 Pays non éligible à Colissimo Europe
 202 Service non autorisé pour cet identifiant
 203 Option international non compatible avec le pays
 300 Pas de point de retrait suite à l’application des règles métier
 301 Pas de point de retrait trouvé
 1000 Erreur système (erreur technique)

 Suivi colis : 
 101 Numéro de colis invalide
 103 Numéro de colis datant de plus de 30 jours
 104 Numéro de colis hors plage client
 105 Numéro de colis inconnu
 201 Identifiant / mot de passe invalide
 202 Service non autorisé pour cet identifiant
 1000 Erreur système (erreur technique) 
</p>";
	   $html .= '</div></div>';

	   $html .= "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black;'>";
	   $html .= "<p style='color:blue; font-size:25px; margin:0 0 0 0;'><strong>Support des Web Services La Poste Colissimo</p>";
	   $html .= "<div id='faqcontent" . __LINE__ . "' class='faqcontent' style='padding: 0px 5px 5px 5px; display:none;'>";
		 $html .= "<p style='color:black;'>Le support des Web Services La Poste Colissimo n'est pas sur le forum Wordpress, mais sur des accès direct au transporteur :</p>";
		 $html .= "<p style='color:black;'>Téléphone 0 241 742 088 . Ou Message en ligne sur votre Colissimo Box : https://www.colissimo.fr/entreprise/mes-demandes/#/home .</p>";
	   $html .= '</div></div>';

   // Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


