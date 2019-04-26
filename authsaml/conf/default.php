<?php
/**
 * Default settings for the authshibboleth plugin.
 *
 * @author  Sixto Martin <sixto.martin.garcia@gmail.com>
 * @author  Andreas Aakre Solberg, UNINETT, http://www.uninett.no
 * @author  François Kooman
 * @author  Thijs Kinkhorst, Universiteit van Tilburg
 * @author  Jorge Hervás <jordihv@gmail.com>, Lukas Slansky <lukas.slansky@upce.cz>
 * @license GPL2 http://www.gnu.org/licenses/gpl.html
 * @link https://github.com/pitbulk/dokuwiki-saml
 *
 * @link https://www.dokuwiki.org/devel:configuration#default_settings Documentation
 */
$conf = array(

    // enable for debugging output
    //'debug' => true,

    /*
     * auth plugin (auth.php)
     */

     // simpleSAMLphp Path: This refers to the path of the simpleSAMLphp folder. For example: /var/www/simplesamlphp
     'simplesaml_path' => "/var/www/simplesamlphp",

     // SimpleSAMLphp SP source: Select the SP source you want to connect to moodle. (Sources are at the SP of simpleSAMLphp in config/authsources.php).
     'simplesaml_authsource' => "default-sp",

     // SAML identify field mapping: It is the SAML attribute that will be mapped to the Dokuwiki username. For example 'uid'.
     'simplesaml_uid' => "uid",

     // SAML identify field mapping: It is the SAML attribute that will be mapped to the Dokuwiki mail. For example 'mail'.
     'simplesaml_mail' => "mail",

     // SAML identify field mapping: It is the SAML attribute that will be mapped to the Dokuwiki name. For example 'cn'.
     'simplesaml_name' => "cn",

     // SAML identify field mapping: It is the SAML attribute that will be mapped to the Dokuwiki groups.
     // For example  'eduPersonAffiliation'.
     'simplesaml_grps' => "eduPersonAffiliation",

     // URL to redirect after successful single logout
     //'simplesaml_logout_url' => "",

    /*
     * action plugin (action.php)
     */

     // When enabled, this will hide the normal login form and redirect directly to the IdP.
     // If the authtype is set to authsaml,  then the redirection will be always done
     'force_saml_login' => false,

     // path to button image
     //'button_image_path' => "",

     // hide local login form
     //'hide_local_login' => true,
);
