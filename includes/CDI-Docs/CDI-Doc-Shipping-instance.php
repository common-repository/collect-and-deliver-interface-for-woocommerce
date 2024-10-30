<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI – Méthode de Livraison', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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
	 $html .= "<p style='color:black;'>La méthode de livraison CDI se veut d’usage général. Les instances de la méthode sont à ajouter dans chacune des zones d’expédition WC (shipping zones) où elle doit opérer. Chaque instance de méthode peut servir plusieurs lignes tarifs, et chaque  zone d’expédition WC peut avoir plusieurs instance de méthode CDI.</p>";
	 $html .= "<p style='color:black;'>Les paramètres initiaux portent sur l’ensemble de l’instance de méthode CDI :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>- Choix du transporteur concerné par cette instance ;</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>- Titre (vu par l’administrateur du site en zone d’expédition) ;</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>- Préfixe (optionnel, texte avant le nom de la ligne tarif, vu par le client internaute).</p>";
	 $html .= "<p style='color:black;'>Ces lignes, ainsi qu’un fichier de démarrage vous sont proposés au démarrage, remplis, dès le choix d’un transporteur, si le tableau des lignes de tarif est vide. A l’enregistrement de ce transporteur, il vous est proposé alors des paramétrages de lignes tarif pour vous guider. Mais tout est modifiable.</p>";

	 $html .= "<p style='color:black;'>« Tableau des lignes de tarif » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Chaque ligne de tarif indique ce qui sera présenté au client internaute et les conditions de cette présentation. Elle comprend : un titre, un tarif fixe HT en euros obligatoire, une partie de prix additionnelle HT en euros variable sous la forme d’une courte séquence PHP,  la ou les classes qui doivent être dans le panier, un « termid » i.e. un identifiant permettant de repérer simplement la ligne pour notamment les « références aux livraisons », un prix minimum HT en euros du panier, un prix maximum HT en euros du panier, un poids minimum en grammes du panier, un poids maximum en grammes du panier.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>La décision de présenter ou non la ligne  nécessite que tous les critères de sélection cités au-dessus soient remplis. Pour la sélection des classes (qui est en multicritères), il suffit qu’un produit avec une des classes sélectionnées soit dans le panier.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>La façon dont les critères de sélection sont appliqués changera si des modes de fonctionnement de l’instance de méthode de livraison sont cochés (voir ci-dessous).</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le statut fiscal des frais d’expédition (TVA ou non) s’applique à l’ensemble des lignes du tableau, en lien avec l’option de choix de  taux de TVA sur livraison indiqué dans les réglages WC.</p>";

	 $html .= "<p style='color:black;'>« Modes » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Ils définissent des modes de fonctionnement particuliers par rapport à la description ci-dessus. Leurs usages correspondent à des cas précis qu’il convient de maîtriser avant de cocher. Elles sont combinatoires :</p>";
		 $html .= "<p style='color:black; margin-left:4em;'>-  « Exclure les classes de livraison » : La sélection de ligne sur les classes de produit ne se fait que si aucun produit ayant une des classes sélectionnées n’est dans le panier ;</p>";
		 $html .= "<p style='color:black; margin-left:4em;'>- « Inclure les taxes dans le prix du panier lors des calculs » : les calculs sur sélection de la fourchette de prix du panier, se fait sur des prix incluant leur TVA (et non sur des prix HT) ;</p>";
		 $html .= "<p style='color:black; margin-left:4em;'>- « Calcul basé sur le prix remise déduite » :  dans l’hypothèse où il y a une remise appliquée (ex. par coupon WC) les calculs sur sélection de la fourchette de prix du panier, se fait sur le prix du panier en ayant ensuite déduit la remise appliquée.</p>";
		 $html .= "<p style='color:black; margin-left:4em;'>- « Inclure le poids du colis vide lors des calculs » : les calculs sur sélection de la fourchette de poids du panier, se fait sur le poids du panier (des produits) auquel on ajoute le poids du colis vide (la tare) indiquée dans les réglages généraux de CDI ;</p>";
		 $html .= "<p style='color:black; margin-left:4em;'>- « WC shipping packages » : La sélection en standard se fait sur l’ensemble des articles du panier. Dans les cas de « market places » ou d’abonnement, utilisant la fonction « WC shipping packages » de Woocommerce, il peut être nécessaire d’appliquer les sélections de poids et de prix uniquement sur chaque lot d’expédition courant.</p>";

	 $html .= "<p style='color:black;'>« Promos » : </p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Cette fonction n’est pas une duplication de la fonction promo de WC qui elle s’applique sur le prix du panier. La fonction promos de CDI  étend la fonction WC en permettant ou non l’activation de l’instance courante de méthode CDI. Le mode « exclure » permet au contraire de n’activer l’instance que quand la promo n’est pas reconnue.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Cette fonction permet notamment d’appliquer des réductions promos uniquement sur le prix de la livraison (et non sur le prix total du panier).</p>";

	 $html .= "<p style='color:black;'>« Macros classes » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Les macros-classes d'expédition sont des expressions booléennes (en syntaxe php avec uniquement les opérateurs and, or, not  ) sur les classes Woocommerce des produits du panier, les expressions parenthésées étant permises. Comme les classes Woocommerce, les macros-classes permettent une sélection au niveau des lignes tarifs de la Méthode de livraison CDI . Elles enrichissent ainsi les possibilités de sélection. </p>";

   // Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


