<?php
/**
 * Configuration texts for plainsaml
 *
 * @author Dominik Volkamer <dominik.volkamer@fau.de> (RRZE)
 * @license GPL2 http://www.gnu.org/licenses/gpl.html
 */

$lang['debug'] = 'Debug-Ausgabe für das Plugin plainsaml einschalten';

$lang['simplesaml_path'] = 'SimpleSAMLphp Pfad: Pfad zu SimpleSAMLphp-Installation. Zum Beispiel: /var/www/simplesamlphp';
$lang['simplesaml_authsource'] = 'SimpleSAMLphp SP-Quelle: Mit welcher SP-Quelle soll sich SimpleSAMLphp verbinden (verfügbare Quellen finden sich in SimpleSAMLphp in der Datei config/authsources.php).';
$lang['simplesaml_uid'] = 'SAML Feld-Mapping: Das SAML-Attribut das auf das Dokuwiki-Attribut \'username\' gemappt werden soll. Zum Beispiel \'uid\'.';
$lang['simplesaml_mail'] = 'SAML Feld-Mapping: Das SAML-Attribut das auf das Dokuwiki-Attribut \'mail\' gemappt werden soll.  Zum Beispiel \'mail\'.';
$lang['simplesaml_name'] = 'SAML Feld-Mapping: Das SAML-Attribut das auf das Dokuwiki-Attribut \'name\' gemappt werden soll. Zum Beispiel \'cn\'.';
$lang['simplesaml_grps'] = 'SAML Feld-Mapping: Das SAML-Attribut das auf das Dokuwiki-Attribut \'groups\' gemappt werden soll. Zum Beispiel  \'eduPersonAffiliation\'';
$lang['simplesaml_logout_url'] = 'Redirect-URL nach einem erfolgreichen Single-Logout (SLO)';

$lang['force_saml_login'] = 'Direkt zum IdP weiterleiten (wenn die Action \'login\' or \'denied\' aufgerufen wird).';
$lang['button_image_url'] = 'URL zur Bilddatei für den benutzerdefinierten SAML-Login-Knopf (Bild kann mit dem Medien-Manager hochgeladen werden)';
$lang['hide_local_login'] = 'Lokales Login-Formular ausblenden, zum Beispiel wenn authplain nur für den remoteuser genutzt werden soll';
