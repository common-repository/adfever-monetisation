=== Plugin Name ===
Contributors: adfever
Donate link: http://www.adfever.com/
Tags: adfever, publicité, advertising, sponsored links, monetization, ads, pub, adfever.com, ad, fever
Requires at least: 3.4
Tested up to: 3.9
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin de monétisation pour les membres de la régie publicitaire AdFever.

== Description ==

Ce plugin permet à tous les éditeurs inscrits sur AdFever - [AdFever.com](http://www.Adfever.com/ "Plateforme de monétisation AdFever") - d'ajouter simplement de nombreux formats de monétisation. Actuellement, les formats publicitaires disponibles sont : liens sponsorisés, liens texte, footer, site under et slide in.

Pour que ce plugin fonctionne, vous devez posséder un compte éditeur AdFever et demander à editeurs@adfever.com  un identifiant pour chaque format.
Les liens sponsorisés sont personnalisables (automatiquement ou manuellement), ils pourront être affichés automatiquement avant ou après vos articles. Les annonces affichées dépendent des tags associés à vos posts et du titre de vos posts.

== Installation ==

1. Uploadez le dossier 'adfever-monetisation' dans le répertoire '/wp-content/plugins/'
2. Activez le plugin via la page 'Extensions' de votre interface d'administration
3. Configurez le plugin via la page 'AdFever Monétisation' de votre interface d'administration
4. Renseignez votre SID AdFever et activez les formats publicitaires souhaités.
5. Personnalisez l'affichage des annonces

Si vous désirez intégrer manuellement les annonces de liens sponsorisés à votre thème wordpress, vous devrez insérer le code suivant à l'intérieur de la "Loop" de wordpress.
`<?php if function_exists(AF_theme_callback) {AF_theme_callback(get_the_ID());}; ?>`


== Frequently Asked Questions ==

= Ou récupérer mes identifiants AdFever =

Pour récupérer vos identifiants, envoyez un email à editeurs@adfever.com

= Les annonces ne s'affichent pas =

* Vérifiez que vous avez bien renseigné votre AID et SID dans la page de configuration du plugin.
* Vérifiez que le fichier header.php de votre thème contienne bien la ligne `<?php wp_head(); ?>`.
* Essayez d'ajouter des tags plus génériques à votre article.

== Screenshots ==

1. Page de configuration du plugin

== Changelog ==

= 1.0.2 =
* Résolution d'un bug dans l'interface d'administration des liens sponsorisés.

= 1.0.1 =
* Résolution d'un bug d'affichage lorsque le format Footer est activé.

= 1.0 =
* Ajout du format publicitaire Footer
* Ajout ddu format publicitaire Slide In
* Ajout du format Site Under
* Ajout des liens In Text
* Ajout d'une page d'options générales

= 0.9.3 =
* Version stable du plugin
* Affichage et configurations liens sponsorisés
