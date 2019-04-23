<?php

/**
 * DokuWiki Plugin authsaml (Auth Component).
 *
 * @author  Sixto Martin <sixto.martin.garcia@gmail.com>
 * @author  Andreas Aakre Solberg, UNINETT, http://www.uninett.no
 * @author  François Kooman
 * @author  Thijs Kinkhorst, Universiteit van Tilburg
 * @author  Jorge Hervás <jordihv@gmail.com>, Lukas Slansky <lukas.slansky@upce.cz>
 * @license GPL2 http://www.gnu.org/licenses/gpl.html
 * @link https://github.com/pitbulk/dokuwiki-saml
 */

// must be run within Dokuwiki
if (! defined('DOKU_INC'))
    die();


class auth_plugin_authsaml extends auth_plugin_authplain {
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

        $this->cando['external'] = true;
        $this->cando['logoff'] = true;
        $this->success = true;

        require_once('saml.php');
        $this->loadConfig();
        $this->saml = new saml_handler($this->conf);
    }


    /**
     * Get user data
     *
     * @return string|null
     */

    public function getUserData($user, $requireGroups = true) {
        $this->saml->debug_saml("Called function 'getUserData($user, $requireGroups)'", __LINE__, __FILE__);
        return $this->saml->getUserData($user);
    }

    /**
     * {@inheritdoc}
     * @see DokuWiki_Auth_Plugin::trustExternal()
     */
    public function trustExternal($user, $pass, $sticky = false) {
        $this->saml->debug_saml("Called function 'trustExternal($user, $pass, $sticky)'", __LINE__, __FILE__);
        $this->saml->get_ssp_instance();

        if ($this->saml->ssp->isAuthenticated()) {
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
        }

        return auth_login($user, $pass, $sticky);
    }

    public function logOff() {
        $this->saml->debug_saml("Called function 'logOff()'", __LINE__, __FILE__);
        if ($this->saml->ssp->isAuthenticated()) {
            $this->saml->slo();
        }
    }
}
