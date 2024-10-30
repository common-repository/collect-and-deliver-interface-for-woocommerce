<?PHP

/**
 * This file is part of the CDI plugin.
 * (c) Halyra
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


   $html = "<div id='faqtitre" . __LINE__ . "' class='faqtitre' style='border:1px solid black; margin: 5px 5px 5px 5px;'>";
   $html .= "<p style='display:inline; color:blue; font-size:25px; margin:0 0 0 0;'><span style='display:inline;'><strong>" . __( 'CDI - CN23', 'cdi' ) . "</strong></span><span style='display:inline; float:right; color:gray;'><strong> ? Aide ? </strong></span></p>";
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

	 $html .= "<p style='color:black;'>Cette page de réglages concernent les réglages relatifs aux envois internationaux. C’est le CN23 qui occupe l’essentiel d’où le nom de la page.</p>";
	 $html .= "<p style='color:black;'>«Paramètres CN23 par défaut » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le CN23 est un standard douanier international destiné à décrire les contenus des colis. Son utilisation est obligatoire dès lors que le colis traverse un espace douanier. Dans l’Union Européenne il n’y a pas de CN23 à utiliser. Par contre une expédition vers un département d’Outre-mer français sort de l’espace douanier européen et fera donc l’objet d’un CN23.  Ce sont les transporteurs et CDI qui s’occupent de déterminer quelles destinations font l’objet d’un  CN23.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Les données sont soient des valeurs par défaut pour l’établissement de chaque CN23, soit des valeurs à reprendre depuis les articles de la commande WC.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Une limite de nombre max d’articles par colis existe, mais elle est modifiable si certaines exploitations le nécessite.</p>";
	 $html .= "<p style='color:black;'>« EORI de l'exportateur » :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Dans certaines situations il est préférable d’indiquer l’EORI de l’exportateur (du e-marchand en général). L’EORI est le numéro fiscal français de l’entreprise encapsulé dans une norme internationale.</p>";

	 $html .= "<p style='color:black;'>«Pays UE exemptés de cn23 et  Codes postaux en exception» :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le cas général est que les colis destinés à des pays membres de l’Union Européenne (mais pas vers les pays rattachés) sont exemptés de présenter des CN23. Il existe toutefois des exceptions (pour lesquels il faut produire des CN23) vers certains territoires particuliers de l’UE, que l’on repère via leurs codes postaux.</p>";

	 $html .= "<p style='color:black;'>«Retours depuis des pays imposant des formalités douanières» :</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Le service des retours automatisés n'est pas assuré depuis les pays imposant des formalités dounières tel le cn23. Il convient alors au e-marchand de convenir au cas par cas, avec son client internaute, des modalités de retour à appliquer.</p>";
	   $html .= "<p style='color:black; margin-left:2em;'>Pour les cas d’exception, l'administrateur du site (mais non le client) a la possibilité de générer un CN23 depuis la Metabox CDI de l'ordre. Pour le client internaute, ce CN23 ne sera pas visible dans sa vue de commande. Il appartient à l'administrateur de le communiquer à son client par mail.</p>";


   // Fin de l'ensemble Doc
   $html .= '</div></div>';
   $html .= '<p> </p>';

   echo wp_kses_post( $html );


