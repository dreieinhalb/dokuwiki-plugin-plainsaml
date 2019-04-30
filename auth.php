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
        global $INPUT, $USERINFO;

        $this->debug_saml("Calling trustExternal with user '$user'.", 3, __LINE__, __FILE__);

        $session = $_SESSION[DOKU_COOKIE]['auth'];
        $saml_session = $_SESSION[DOKU_COOKIE]['auth']['saml'];

        if((!empty($user) && !empty($pass)) || (!empty($session) && empty($saml_session))) {
            $this->debug_saml("Using authplain login in trustExternal function.", 1, __LINE__, __FILE__);

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
            $this->debug_saml("Using saml login in trustExternal function.", 1, __LINE__, __FILE__);

            $ssp = $this->saml->get_ssp_instance();

            if ($ssp->isAuthenticated()) {

                $session = $_SESSION[DOKU_COOKIE]['auth'];

                // check session for existing valid saml session
                if(isset($session['saml'])) {
                    if(($session['time'] >= time() - $conf['auth_security_timeout']) &&
                        ($session['buid'] == auth_browseruid())
                    ) {
                        $_SERVER['REMOTE_USER'] = $session['user'];
                        $USERINFO               = $session['info'];
                        $this->saml->debug_saml("Existing valid saml session found!", 1, __LINE__, __FILE__);
                        return true;
                    }
                }

                $username = $this->saml->getUsername();

                if($this->saml->getUserData($username)) {
                    $this->saml->update_user($username);
                    $this->saml->login($username);
                    $this->saml->debug_saml("Login existing user '$username'!", 3, __LINE__, __FILE__);
                    return true;
                } else {
                    if($this->saml->register_user($username)) {
                        $this->saml->login($username);
                        $this->saml->debug_saml("Login new registered user '$username'!", 3, __LINE__, __FILE__);
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
