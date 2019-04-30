<?php
/**
 * Configuration texts for plainsaml
 *
 * @author Dominik Volkamer <dominik.volkamer@fau.de> (RRZE)
 * @author Sixto Martin <sixto.martin.garcia@gmail.com>
 * @author Andreas Aakre Solberg, UNINETT, http://www.uninett.no
 * @author François Kooman
 * @author Thijs Kinkhorst, Universiteit van Tilburg
 * @author Jorge Hervás <jordihv@gmail.com>, Lukas Slansky <lukas.slansky@upce.cz>
 * @license GPL2 http://www.gnu.org/licenses/gpl.html
 */

$lang['debug'] = 'Debug level for plainsaml plugin (from: 0 = no debug messages, to: 3 = all debug messages). Debug messages are written to the error log of PHP, which is typically the web server error log.';

$lang['simplesaml_path'] = 'SimpleSAMLphp path: This refers to the path of the SimpleSAMLphp folder. For example: /var/www/simplesamlphp';
$lang['simplesaml_authsource'] = 'SimpleSAMLphp SP source: Select the SP source you want to connect (Sources are at the SP of simpleSAMLphp in config/authsources.php).';
$lang['simplesaml_uid'] = 'SAML identify field mapping: It is the SAML attribute that will be mapped to the Dokuwiki username. For example \'uid\'.';
$lang['simplesaml_mail'] = 'SAML identify field mapping: It is the SAML attribute that will be mapped to the Dokuwiki mail address. For example \'mail\'.';
$lang['simplesaml_name'] = 'SAML identify field mapping: It is the SAML attribute that will be mapped to the Dokuwiki name. For example \'cn\'.';
$lang['simplesaml_grps'] = 'SAML identify field mapping: It is the SAML attribute that will be mapped to the Dokuwiki groups. For example  \'eduPersonAffiliation\'';
$lang['simplesaml_logout_url'] = 'URL to redirect after successful single logout (SLO)';

$lang['force_saml_login'] = 'Redirect directly to the IdP (when action is \'login\' or \'denied\').';
$lang['button_image_url'] = 'URL to custom button image for SAML login (can be uploaded with media manager)';
$lang['hide_local_login'] = 'Hide local login form, for example if you want to use authplain only for remoteuser';
