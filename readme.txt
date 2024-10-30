=== CDI - Collect and Deliver Interface for Woocommerce === 
Contributors: Halyra
Tags: shipping, colissimo, mondialrelay, ups, collect
Requires at least:  6.2
Tested up to: 6.6.2
Requires PHP: 7.3
Stable tag: 5.5.6
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

L’indispensable entre votre site Woocommerce et vos transporteurs de colis 

== Description ==

Vous cherchez à économiser sur vos préparations et expéditions 20mn/colis au minimum ?

Vous recherchez la sécurité de vos clients pour leurs achats dans votre boutique ?

Vous voulez diversifier vos expéditions (Colossimo, UPS, Mondial Relay, Click&Collect, ...) ?

> #### ... vous etes bien au bon endroit.


= L’essentiel : =

 CDI – Collect and Deliver Interface, c’est la gestion de vos colis sur Woocommerce :

 ... gestion automatisée des colis, de la connexion avec vos différents transporteurs, des informations avec vos clients ;

 ... actuellement 4 transporteurs desservis : La Poste Colissimo, Mondial Relay, UPS, Click&Collect … et plugin extensible ;

 ... des interfaces admin et clients unifiés, indépendants des transporteurs.

*Aux utilisateurs du plugin original «Colissimo Delivery Integration», il assure dès son activation la reprise de leurs paramètres et la compatibilité avec leurs commandes anciennes.*

*CDI est une initiative indépendante, sans lien d’intérêt avec les transporteurs desservis.*

*Et n'oublions pas : CDI est un plugin gratuit pour l'ensemble de ses fonctions.* 

= Résumé des fonctions : =

CDI réalise l’interfaçage de votre installation WooCommerce avec les services des transporteurs de colis. Il dessert actuellement : Colissimo de La Poste, Mondial Relay, UPS, plus un transporteur interne de «Click & Collect». Structure du plugin prévoyant des ajouts futurs à cette liste. 

CDI permet :

* L’utilisation d’une méthode de livraison Woommerce puissante et bien adaptée à toutes situations : sélection des tarifs par classes de produit, fourchette de prix du panier HT ou TTC, fourchette de poids des articles ; tarifs variables programmables ; gestion des coupons promo Woocommerce ; mode inclusion/exclusion pour les classes produit et les coupons promo ; macro-classes pour sélections complexes ; gestion de tarifs prioritaires ; places de marché ; abonnements Woocommerce. Cette méthode est suffisante pour un site Woocommerce même complexe.

* Au client son choix de livraison. Ses données de suivi colis figurent dans les courriels et dans ses vues Woocommerce de commandes. Il dispose d’une extension de l’adresse basique Woocommerce de 2 lignes aux standards internationaux postaux 4 lignes. 

* La gestion de toutes les options des transporteurs: signature, assurance complémentaire, expéditions internationales, type de retour, caractéristiques CN23, … . Le service des retours de colis par le client avec pilotage/autorisation par l’Administrateur du site. Le traitement des points relais des transporteurs avec choix de carte Google Maps ou Open Map. 

* Une gestion des colis des commandes dans une passerelle dédiée, asynchrone du flux Woocommerce, avec un mode automatique pour chacun des transporteur, exécutant en ligne son Web service pour récupérer les étiquettes d’affranchissement et les autres données ; 

* Une gestion automatisée au maximum de l’exploitation des colis : soumission en 1 clic des colis au transporteur, purge automatique des colis traités, vue directe des étiquettes et cn23, impression globale des étiquettes, export global des colis, historique des colis, outil de debug des anomalies d’exploitation, différents documents logistiques.

* Un suivi temps réel dans la console d’administration des commandes, de la situation de délivrance des colis expédiés. 


== FAQ ==

= Où puis-je obtenir de l'aide ou  parler à d'autres utilisateurs ?  =

* Le support est disponible à l'aide de la communauté dans le forum Wordpress de ce plugin.
C'est le meilleur moyen parce que toute la communauté profite des solutions données dans le forum. Vous pouvez y utiliser indifféremment l'anglais ou le français. 

= Puis-je obtenir une personnalisation poussée de CDI ? =

* Une personnalisation de base peut être obtenue par les paramètres CDI. Mais vous pouvez allez plus loin et avoir une personnalisation beaucoup plus fine lorsque vous utilisez des filtres Wordpress installés dans CDI.
 
* Différents exemples d'utilisation des principaux filtres CDI sont donnés dans le répertoire /examples du plugin CDI. Ils sont un guide qu'il vous faudra adapter selon vos objectifs et les exigences propres de Wordpress. 

= Où sont les panneaux de réglages/contrôle de CDI ? =

* Le pilotage de CDI s’effectue à 3 endroits:
   -Dans Woocommerce-> Réglages-> CDI pour les réglages généraux, et ceux propres aux transporteurs ;
   -Dans Woocommerce-> Réglages-> Expédition et dans chaque instance en zone d'expédition pour les paramètres relatifs à une méthode d'expédition CDI ;
   -Dans Woocommerce-> Passerelle CDI pour contrôler la production d'étiquettes des colis, la gestion des colis, les retours, les documents logistiques, etc.
   
* La documentation est incluse dans le plugin lui-même : aides contextuelles (boutons aide, pointeurs souris, descriptifs de champ), et manuels contextuels. 

= Quelles sont les dépendances d'appels externes ? =

* CDI sollicite plusieurs services externes :
   -Les Web services des différents transporteurs ;
   -Des services externes de cartographie comme Google maps et Open layer ;


== Installation ==

1. Installer le plugin de façon traditionnelle depuis Wordpress, et l’activer.
1. Aller dans la page  : Woocommerce -> Réglages -> CDI, et adapter quand nécessaire les réglages des divers onglets. Les réglages par défaut de CDI permettent déjà un fonctionnement immédiat.
1. Renseigner vos réglages des identifiants et mots de passe nécessaires pour les transporteurs, ainsi que la clé Google Maps, si vous utilisez ces fonctions.

= Assistance et support : =

* Le support du plugin CDI est assuré par les participants au forum wordpress.org. 


== Screenshots == 

1. CDI Checkout page Access points.
1. CDI Checkout page seen by customer.
1. CDI Customer interface.
1. CDI icons in Woocommerce orders list.
1. CDI Metabox.
1. CDI Gateway multi cariers.
1. CDI logistic tools.
1. CDI shipping instance.


== Changelog ==

= 5.5.6 (2024-10-26) =
* Add Update the pickup address in WC subscription after change of pickup number
* Add Pickup location label in WC subscription Metabox
* Update Mondial Relay test private key
* Add nonce and files type ckecks for private message sending
* Some typo and fix

= 5.5.3 (2024-09-16) =
* Fix Avoid analysis of attributes no conforme WC
* Extend registering hstariff in global shop terms (Produits->attributs) and in individual product (Données produit -> Attributs)
* Some typo and fix

= 5.5.2 (2024-09-03) =
* Update hstariff coding
* Add Filter examples (Init Métabox)
* Some typo and fix

= 5.5.1 (2024-06-09) =
* Update UPS for OAuth 2.0
* Update UPS for migration xml to RESTful
* Update UPS for internal structure and endpoints
* Update CDI to accept Woocommerce Checkout block
* Some typo and fix

= 5.4.0 (2024-04-16) =
* Fix CDI priority conflict between CDI and  function $woocommerce->customer->get_billing_country()
* Fix S1 country conflict with function $woocommerce->customer->get_billing_country()
* Fix Checking mandatory phone numbers, billing and shipping
* Add Support for the new experimental Woocommerce Checkout block extension
* Some typo and fix

= 5.3.5 (2024-02-06) =
* Add OAuth 2.0 environment (future use for UPS)
* Fix CDI status management for Collect carrier
* Fix Map display when virtual products
* Some typo and fix

= 5.3.4 (2024-01-18) =
* Update to https of Mondial Relay endpoints (Mondial Relay site recently switched to https, and removal of http planned for February 1, 2023)
* Fix Parcel completed date check before returning parcel

= 5.3.3 (2024-01-18) =
* Fix Cart with only no-shipping products
* Add Exclude of CDI Gateway orders with only virtual products 
* Some typo and fix

= 5.3.2 (2023-12-17) =
* Fix Bulk CDI Gateway insertion

= 5.3.1 (2023-11-11) =
* Add HPOS Compatibility for Woocommerce and Woocommerce Subscriptions
* Add Hstariff in products with priority (option)
* Fix Corrected positioning on maps
* Fix Colissimo Bordereau reduced from 200 to 100 for multi lots
* Fix Default GPS positions for Carrier Collect
* Different typos and important fixes

= 5.2.5 (2023-08-07) =
* Fix Enhancement of messages error when geolocation not possible
* Fix Enhancement of CDI synchro function for a stronger mutual exclusion of administrators (case multi admins exploitation)
* Add filter 'cdi_filterstring_retourcolis_eligible_force' for global forcing of processing parcels return
* Some typo and fix

= 5.2.4 (2023-04-08) =
* Fix Management of errors when the carrier cannot be reached
* Some typo and fix

= 5.2.3 (2023-01-14) =
* Fix Css Error of fpdf->Image(url) with last Wordpress versions (workaround)
* Add Click & Collect : protocol added for couriers, and Rework
* Fix Uncaught Exception: Serialization of SimpleXMLElement in Colissimo Remise

= 5.2.2 (2022-12-04) =
* Fix Rework Soap Colissimo  (Direct Soap Interface, V2 WS Laposte, https)
* Fix Gateway Debug display
* Add Label and Cn23 printable from Woocommerce order list (shop_order panel)
* Upgrade Nusoap v 1.124 (used for Mondial Relay carrier)
* Fix Display shop_order details and CDI Metabox
* Review by Wordpress Team
* Fix Soap Colissimo V2 - labelResponse replaced by labelV2Response
* Fix Soap Colissimo V2 - Address fields order
* Some typo and fix

= 5.1.14 (2022-09-10) =
* Add Custom Carrier structure model.
* Add Colissimo CN23 article value round up to 1 euro if less
* Add Files attachment at CDI private message
* Fix Colissimo CN23 shipping cost with decimal and comma as separator
* Fix Strengthening compatibility with PHP8
* Fix Sanitize addresses in Mondial Relay returns
* Some typo and fix

= 5.1.13 (2022-08-16) =
* Update Rework of the "notcdi" carrier (now named Custom by default) to be ready as a full CDI carrier pre-integrable in CDI.
* Update compatibility Macro-class definitions syntax
* Some typo and fix

= 5.1.12 (2022-08-08) =
* Fix Rework of the delivery acknowledgment process for the Collect carrier.
* Add Compatibility with old syntax of Macro-class definitions
* Some typo and fix

= 5.1.11 (2022-07-01) =
* Fix Potential naming conflict with other plugins in shipping.
* Fix Improvement of macroclasses syntax check
* Some typo and fix

= 5.1.10 (2022-06-20) =
* Fix Plugin consolidation and security issues. General review and update for better compliance with Wordpress standards. 
* Warning No functional adaptations, but it is preferable for the update to this new version to first carry out a global test on your test installation.
* Some typo and fix

= 5.1.6 (2022-05-14) =
* Add Filters in CDI-Frontend.php to change tracking information in WC mails and WC order views
* Add Pickup fulladdress in cdi_array_for_carrier
* Some typo and fix

= 5.1.5 (2022-03-24) =
* Fix Calculation of customer coordinates when city is not unique in the country
* Update Better map centering 
* Some typo and fix

= 5.1.4 (2022-03-03) =
* Add Filter to choose the status of CDI parcels entered in CDI Gateway (e.g. for booking process or deferred shipment)
* Add Logistic documents - Additional check for orders still existing after their selection
* Fix Colissimo, MR, UPS  Sanitize city name and street name ("-" and accented letters)
* Fix MR search for "Relay points" from GPS coordinates having less than 7 decimal digits 
* Fix Logistic documents - CSV History display if not encrypted
* Some typo and fix

= 5.1.3 (2022-01-26) =
* Fix Preview display in CDI Gateway
* Fix All jQuery actions accepted in filter 'cdi_filterjava_retrait_whereselectorpickup'
* Add Filters to choose shipping methods in multi shipping packages context (multi vendors)
* Update Examples file
* Add MR CCC - Collecte Client Chargeur
* Add MR Return delivery mode : LCC or 24R
* Update MR Pdf labels process - mass printing
* Some typo and fix

= 5.1.2 (2022-01-09) =
* Add Notice message when wordpress access to carrier url is impossible
* Fix Collect label scan to set delivery status when without security code
* Add Mondial Relay and UPS bypass zipcode for Portugal
* Some typo and fix

= 5.1.1 (2021-12-21) =
* Fix Mondial relay product for IT,BE,LU
* Add Improved customer address entries

= 5.1.0 (2021-12-15) =
* CDI New Open version

