<?php

/**
 * DokuWiki Plugin plainsaml (Action Component)
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

class action_plugin_plainsaml extends DokuWiki_Action_Plugin {

    protected $saml;

    /**
     * Register SAML event handlers
     */

    public function register(Doku_Event_Handler $controller) {
        global $conf;

        if($conf['authtype'] != 'plainsaml') return;

        require_once('saml.php');
        $this->loadConfig();
        $this->saml = new saml_handler($this->conf);

        $controller->register_hook('HTML_LOGINFORM_OUTPUT', 'BEFORE', $this, 'handle_login_form');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'AFTER', $this, 'handle_action_after');
    }

    /**
     * Instead of showing the login page when action is 'login' or 'denied', redirect directly to the IdP if force_saml_login is 'true'
     * (adapted parts from authplaincas by Erasmus Student Network @esn-org)
     */

    function handle_action_after(&$event, $param) {
        global $ACT;
        global $INFO;

        $force_saml = $this->getConf('force_saml_login');

        if((($ACT == 'denied' && empty($USERINFO)) || $ACT == 'login') && $force_saml) {
            $this->saml->debug_saml("Forcing SAML login because 'force_saml_login' is true.", 3, __LINE__, __FILE__);

            $this->saml->get_ssp_instance();
            $this->saml->ssp->requireAuth();
        }
    }

    /**
     * Insert button for SAML login and hide local login form (if config option is set)
     */

    function handle_login_form(&$event, $param) {
        global $auth, $conf;

        $this->saml->get_ssp_instance();

        // remove the local login form if config option is set
        if (!empty($this->getConf('hide_local_login')) && $this->getConf('hide_local_login') == true) {
            $this->saml->debug_saml("Hiding local login form because config option is set.", 3, __LINE__, __FILE__);
            $event->data->_content = array();
        }

        // use custom path for button image (or fall back to default)
        $button_image = 'lib/plugins/plainsaml/img/button.gif';
        if (!empty($this->getConf('button_image_url'))) {
            $this->saml->debug_saml("Using custom button image because config option is set.", 3, __LINE__, __FILE__);
            $button_image = $this->getConf('button_image_url');
        }

        // add button for SAML login to login page
        $fieldset  = '<fieldset height="400px" style="margin-bottom:20px;"><legend padding-top:-5px">'.$this->getLang('saml_connect').'</legend>';
        $fieldset .= '<center><a href="'.$this->saml->ssp->getLoginURL().'"><img src="'.$button_image.'" alt="'.$this->getLang('saml_connect').'"></a><br>';
        $fieldset .= $this->getLang('login_link').'</center></fieldset>';
        $pos = $event->data->findElementByAttribute('type', 'submit');
        $event->data->insertElement($pos-4, $fieldset);
    }
}
