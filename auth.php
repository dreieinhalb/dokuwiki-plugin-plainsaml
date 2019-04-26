<?php

/**
 * DokuWiki Plugin plainsaml (Auth Component).
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

class auth_plugin_plainsaml extends auth_plugin_authplain {
    /**
     * simplesamlphp auth instance
     *
     * @var object
     */
    protected $saml;

    /**
     * Constructor.
     */
    public function __construct() {
        global $conf;

        parent::__construct();

        $this->cando['modLogin'] = false;
        $this->cando['modPass'] = false;
        $this->cando['modName'] = false;
        $this->cando['modMail'] = false;
        $this->cando['modGroups'] = false;
        $this->cando['getUsers'] = false;
        $this->cando['getUserCount'] = false;
        $this->cando['external'] = true;
        $this->cando['logoff'] = true;

        $this->success = true;

        require_once('saml.php');
        $this->loadConfig();
        $this->saml = new saml_handler($this->conf);
    }


    /**
     * {@inheritdoc}
     * @see DokuWiki_Auth_Plugin::trustExternal()
     */
    public function trustExternal($user, $pass, $sticky = false) {
        global $INPUT;

        $this->saml->debug_saml("Called function 'trustExternal($user,[...])'", __LINE__, __FILE__);

        $session = $_SESSION[DOKU_COOKIE]['auth'];
        $saml_session = $_SESSION[DOKU_COOKIE]['auth']['saml'];

        if((!empty($user) && !empty($pass)) || (!empty($session) && empty($saml_session))) {
            $this->saml->debug_saml("using authplain", __LINE__, __FILE__);

            $this->cando['addUser'] = true;
            $this->cando['delUser'] = true;
            $this->cando['modLogin'] = true;
            $this->cando['modPass'] = true;
            $this->cando['modName'] = true;
            $this->cando['modMail'] = true;
            $this->cando['modGroups'] = true;
            $this->cando['getUsers'] = true;
            $this->cando['getUserCount'] = true;
            $this->cando['logoff'] = true;

            return auth_login($user, $pass, $sticky);
        } else {
            $this->saml->debug_saml("using saml", __LINE__, __FILE__);
            $ssp = $this->saml->get_ssp_instance();

            if ($ssp->isAuthenticated()) {
                $username = $this->saml->getUsername();

                if($this->saml->getUserData($username)) {
                    $this->saml->update_user($username);
                    $this->saml->login($username);
                    return true;
                } else {
                    if($this->saml->register_user($username)) {
                        $this->saml->login($username);
                        return true;
                    }
                }
            } else {
                // session cleanup according to SimpleSAMLphp documentation if user is not authenticated
                $this->saml->session_cleanup();
            }
       }
       //just to be sure
       auth_logOff(true);
       return false;
    }

    public function logOff() {
        if (empty($_SESSION[DOKU_COOKIE]['auth']['saml'])){
            parent::logOff();

            if(isset($_SESSION[DOKU_COOKIE]['auth'])) {
                unset($_SESSION[DOKU_COOKIE]['auth']);
            }
        } else {
            $this->saml->debug_saml("Called function 'logOff()'", __LINE__, __FILE__);
            $ssp = $this->saml->get_ssp_instance();

            if ($this->saml->ssp->isAuthenticated()) {
                $this->saml->slo();
            } else {
                // session cleanup according to SimpleSAMLphp documentation if user is not authenticated
                $this->saml->session_cleanup();
            }
        }
    }

    public function getUserData($user, $requireGroups = true) {
        // search first for SAML user and fallback to local user
        return $this->saml->getUserData($user) ? $this->saml->getUserData($user) : parent::getUserData($user);
    }
}
