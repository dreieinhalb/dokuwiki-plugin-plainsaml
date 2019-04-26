<?php

/**
 * DokuWiki Plugin authsaml (Action Component)
 *
 * Can intercepts the 'login' action and redirects the user to the IdP
 * instead of showing the login form.
 *
 * @author  Dominik Volkamer <dominik.volkamer@fau.de> (RRZE), Oleg Britvin <oleg.britvin@fau.de> (RRZE)
 * @author  Sixto Martin <sixto.martin.garcia@gmail.com>
 * @author  Andreas Aakre Solberg, UNINETT, http://www.uninett.no
 * @author  François Kooman
 * @author  Thijs Kinkhorst, Universiteit van Tilburg
 * @author  Jorge Hervás <jordihv@gmail.com>, Lukas Slansky <lukas.slansky@upce.cz>

 * @license GPL2 http://www.gnu.org/licenses/gpl.html
 * @link https://github.com/pitbulk/dokuwiki-saml
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_authsaml extends DokuWiki_Action_Plugin {

    protected $saml;

    /**
     * Register SAML event handlers
     */

    public function register(Doku_Event_Handler $controller) {
        global $conf;

        if($conf['authtype'] != 'authsaml') return;

        require_once('saml.php');
        $this->loadConfig();
        $this->saml = new saml_handler($this->conf);

        $controller->register_hook('HTML_LOGINFORM_OUTPUT', 'BEFORE', $this, 'handle_login_form');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_login');
        $controller->register_hook('AUTH_ACL_CHECK', 'AFTER', $this, 'handle_access_denied');
    }

    /**
     * Instead of showing the login page when access is denied, redirect to the IdP if force_saml_login is 'true'
     */

    function handle_access_denied(&$event, $param) {
        $this->saml->debug_saml("Called function 'handle_access_denied(&$event, $param)'", __LINE__, __FILE__);
        global $ACT;
        global $INFO;

        $this->saml->get_ssp_instance();

        if ($ACT == 'denied') {
            if ($this->getConf('force_saml_login')) {
                $this->saml->ssp->requireAuth();
            }
        }
    }

    /**
     * Redirect Login Handler. Redirect to the IdP if force_saml_login is True
     */

    public function handle_login(&$event, $param) {
        $this->saml->debug_saml("Called function 'handle_login($event, $param)'", __LINE__, __FILE__);
        global $ACT, $auth;

        $this->saml->get_ssp_instance();

        if ('login' == $ACT) {
            $force_saml_login = $this->getConf('force_saml_login');
            if ($force_saml_login) {
                $this->saml->ssp->requireAuth();
            }
        }
    }

    /**
     * Insert button for SAML login and hide local login form (if config option is set)
     */

    function handle_login_form(&$event, $param) {
        $this->saml->debug_saml("Called function 'handle_login_form(&$event, $param)'", __LINE__, __FILE__);
        global $auth, $conf;

        $this->saml->get_ssp_instance();

        // remove the local login form if config option is set
        if (!empty($this->getConf('hide_local_login')) && $this->getConf('hide_local_login') == true) {
            $event->data->_content = array();
        }

        // use custom path for button image (or fall back to default)
        $button_image_path = 'lib/plugins/authsaml/images/button.gif';
        if (!empty($this->getConf('button_image_path')) && is_file($this->getConf('button_image_path'))) {
            $button_image_path = $this->getConf('button_image_path');
        }

        // add button for SAML login to login page
        $fieldset  = '<fieldset height="400px" style="margin-bottom:20px;"><legend padding-top:-5px">'.$this->getLang('saml_connect').'</legend>';
        $fieldset .= '<center><a href="'.$this->saml->ssp->getLoginURL().'"><img src="'.$button_image_path.'" alt="'.$this->getLang('saml_connect').'"></a><br>';
        $fieldset .= $this->getLang('login_link').'</center></fieldset>';
        $pos = $event->data->findElementByAttribute('type', 'submit');
        $event->data->insertElement($pos-4, $fieldset);
    }
}
