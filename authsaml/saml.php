<?php

/**
 * DokuWiki Plugin authsaml (SAML class)
 *
 * @author  Sixto Martin <sixto.martin.garcia@gmail.com>
 * @author  Andreas Aakre Solberg, UNINETT, http://www.uninett.no
 * @author  François Kooman
 * @author  Thijs Kinkhorst, Universiteit van Tilburg / SURFnet bv
 * @author  Jorge Hervás <jordihv@gmail.com>, Lukas Slansky <lukas.slansky@upce.cz>

 * @license GPL2 http://www.gnu.org/licenses/gpl.html
 * @link https://github.com/pitbulk/dokuwiki-saml
 */

class saml_handler {

    protected $simplesaml_path;
    protected $simplesaml_authsource;
    protected $simplesaml_uid;
    protected $simplesaml_mail;
    protected $simplesaml_name;
    protected $simplesaml_grps;
    protected $simplesaml_logout_url;
    protected $defaultgroup;

    protected $debug_saml;

    protected $force_saml_login;

    protected $saml_user_file;
    protected $users;

    public $ssp = null;
    protected $attributes = array();


    public function __construct($plugin_conf) {
        global $auth, $conf;

        $this->defaultgroup = $conf['defaultgroup'];

        $this->debug_saml = $plugin_conf['debug'];

        $this->simplesaml_path = $plugin_conf['simplesaml_path'];
        $this->simplesaml_authsource = $plugin_conf['simplesaml_authsource'];
        $this->simplesaml_uid = $plugin_conf['simplesaml_uid'];
        $this->simplesaml_mail = $plugin_conf['simplesaml_mail'];
        $this->simplesaml_name = $plugin_conf['simplesaml_name'];
        $this->simplesaml_grps = $plugin_conf['simplesaml_grps'];
        $this->simplesaml_logout_url = $plugin_conf['simplesaml_logout_url'];

        $auth_saml_active = isset($conf['authtype']) && $conf['authtype'] == 'authsaml';

        // Force redirection to the IdP if we chose authsaml as authtype or if we configured as true the 'force_saml_login'
        $force_saml_login = $auth_saml_active || $plugin_conf['force_saml_login'];

        # Use DOKU_CONF analogue to authplain plugin
        $this->saml_user_file = DOKU_CONF.'users.saml.php';
    }

    /**
     *  Get a simplesamlphp auth instance (initiate it when it does not exist)
     */
    public function get_ssp_instance() {
        if ($this->ssp == null) {
            include_once($this->simplesaml_path.'/lib/_autoload.php');
            $this->ssp = new SimpleSAML_Auth_Simple($this->simplesaml_authsource);
        }
        return $this->ssp;
    }

    public function checkPass($user) {
        $this->debug_saml("Called function 'checkPass($user)'", __LINE__, __FILE__);
        $ssp = $this->get_ssp_instance();
        if ($ssp->isAuthenticated()) {
            if ($user == $this->getUsername()) {
                return true;
            }
        }
        return false;
    }

    public function slo() {
        $this->debug_saml("Called function 'slo()'", __LINE__, __FILE__);
        if ($this->ssp->isAuthenticated()) {
            # redirect to logout URL after successful single logout
            if (empty($this->simplesaml_logout_url)) {
                $this->ssp->logout();
            } else {
                $this->ssp->logout($this->simplesaml_logout_url);
            }
        }
    }

    public function getUsername() {
        $this->debug_saml("Called function 'getUsername()'", __LINE__, __FILE__);
        $attributes = $this->ssp->getAttributes();
        return $attributes[$this->simplesaml_uid][0];
    }


    /**
     * Get user data from the SAML assertion
     *
     * @return array|false
     */
    public function getSAMLUserData() {
        $this->debug_saml("Called function 'getSAMLUserData()'", __LINE__, __FILE__);
        $this->attributes = $this->ssp->getAttributes();

        if (!empty($this->attributes)) {
            if (!array_key_exists($this->simplesaml_name , $this->attributes)) {
              $name = "";
            } else {
              $name = $this->attributes[$this->simplesaml_name][0];
            }

            if (!array_key_exists($this->simplesaml_mail , $this->attributes)) {
              $mail = "";
            } else {
              $mail = $this->attributes[$this->simplesaml_mail][0];
            }

            if (!array_key_exists($this->simplesaml_grps, $this->attributes) ||
              empty($this->attributes[$this->simplesaml_grps])) {
                $grps = array();
            } else {
                $grps = $this->attributes[$this->simplesaml_grps];
            }

            return array(
                'name' => $name,
                'mail' => $mail,
                'grps' => $grps
            );
        }
        return false;
    }

    /**
     * Get user data from the saml store user file
     *
     * @return array|false
     */
    public function getUserData($user) {
        $this->debug_saml("Called function 'getUserData($user)'", __LINE__, __FILE__);
        if(empty($this->users) || empty($this->users[$user])) $this->_loadUserData();
        return isset($this->users[$user]) ? $this->users[$user] : false;
    }

    function login($username) {
        $this->debug_saml("Called function 'login($username)'", __LINE__, __FILE__);
        global $conf, $USERINFO;

        $ssp = $this->get_ssp_instance();
        $this->attributes = $ssp->getAttributes();


        if ($ssp->isAuthenticated() && !empty($this->attributes)) {

            $_SERVER['REMOTE_USER'] = $username;

            $userData = $this->getUserData($username);

            $USERINFO['name'] = $userData['name'];
            $USERINFO['mail'] = $userData['mail'];
            $USERINFO['grps'] = $userData['grps'];

            // set cookie
            $sticky = false;
            $secret = auth_cookiesalt(!$sticky, true); //bind non-sticky to session
            $pass = auth_encrypt($userData['pass'], $secret);
            $cookie    = base64_encode($username).'|'.((int) $sticky).'|'.base64_encode($pass);
            $cookieDir = empty($conf['cookiedir']) ? DOKU_REL : $conf['cookiedir'];
            $time      = $sticky ? (time() + 60 * 60 * 24 * 365) : 0; //one year
            setcookie(DOKU_COOKIE, $cookie, $time, $cookieDir, '', ($conf['securecookie'] && is_ssl()), true);

            // set session
            $_SESSION[DOKU_COOKIE]['auth']['user'] = $username;
            $_SESSION[DOKU_COOKIE]['auth']['pass'] = sha1($pass);
            $_SESSION[DOKU_COOKIE]['auth']['buid'] = auth_browseruid();
            $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
            $_SESSION[DOKU_COOKIE]['auth']['time'] = time();
        }
    }

    function register_user($username) {
        $this->debug_saml("Called function 'register_user($username)'", __LINE__, __FILE__);
        global $auth;
        $user = $username;
        $pass = auth_pwgen();

        $userData = $this->getSAMLUserData();

        return $this->_saveUserData($username, $pass, $userData);
    }

    function update_user($username) {
        $this->debug_saml("Called function 'update_user($username)'", __LINE__, __FILE__);
        global $auth, $conf;

        $changes = array();
        $userData = $this->getSAMLUserData();

        if ($auth->canDo('modName')) {
            if(!empty($userData['name'])) {
                $changes['name'] = $userData['name'];
            }
        }
        if ($auth->canDo('modMail')) {
            if(!empty($userData['mail'])) {
                $changes['mail'] = $userData['mail'];
            }
        }
        if ($auth->canDo('modGroups')) {
            if(!empty($userData['grps'])) {
                $changes['grps'] = $userData['grps'];
            }
        }

        if (!empty($changes)) {
            $this->modifyUser($username, $changes);
        }
    }

    function delete_user($users) {
        $this->debug_saml("Called function 'delete_user($users)'", __LINE__, __FILE__);
        return $this->deleteUsers($users);
    }

    /**
     * Load all user data (modified copy from plain.class.php)
     *
     * loads the user file into a datastructure
     *
     * @author  Lukas Slansky <lukas.slansky@upce.cz>
     */
    function _loadUserData() {
        $this->debug_saml("Called function '_loadUserData()'", __LINE__, __FILE__);
        global $conf;

        $this->users = array();

        if(!@file_exists($this->saml_user_file))
            return;

        $lines = file($this->saml_user_file);
        foreach($lines as $line){
            $line = preg_replace('/#.*$/','',$line); //ignore comments
            $line = trim($line);
            if(empty($line)) continue;

            $row = explode(":",$line,6);
            $groups = array_map('urldecode', array_values(array_filter(explode(",",$row[4]))));

            $this->users[$row[0]]['pass'] = $row[1];
            $this->users[$row[0]]['name'] = urldecode($row[2]);
            $this->users[$row[0]]['mail'] = $row[3];
            $this->users[$row[0]]['grps'] = $groups;
        }
    }

    /**
     * Save user data
     *
     * saves the user file into a datastructure
     *
     * @author  Lukas Slansky <lukas.slansky@upce.cz>
     */
    function _saveUserData($username, $pass, $userData) {
        $this->debug_saml("Called function '_saveUserData()'", __LINE__, __FILE__);
        global $conf;

        $pattern = '/^' . $username . ':/';

        // Delete old line from users file
        if (!io_deleteFromFile($this->saml_user_file, $pattern, true)) {
          msg('Error saving user data (1)', -1);
          return false;
        }
        if ($userData['grps'] == null) {
            $userData['grps'] = array();
        }

        $pass = auth_cryptPassword($pass);
        $groups = join(',',array_map('urlencode',$userData['grps']));
        $userline = join(':',array($username, $pass, $userData['name'], $userData['mail'], $groups))."\n";

        // Save new line into users file
        if (!io_saveFile($this->saml_user_file, $userline, true)) {
          msg('Error saving user data (2)', -1);
          return false;
        }
        $this->users[$username] = $userinfo;
        return true;
    }

    public function deleteUsers($users) {
        $this->debug_saml("Called function '_deleteUsers()'", __LINE__, __FILE__);

        if(!is_array($users) || empty($users)) return 0;

        if($this->users === null) $this->_loadUserData();

        $deleted = array();
        foreach($users as $user) {
            if(isset($this->users[$user])) $deleted[] = preg_quote($user, '/');
        }

        if(empty($deleted)) return 0;

        $pattern = '/^('.join('|', $deleted).'):/';

        if(io_deleteFromFile($this->saml_user_file, $pattern, true)) {
            foreach($deleted as $user) unset($this->users[$user]);
            return count($deleted);
        }

        // problem deleting, reload the user list and count the difference
        $count = count($this->users);
        $this->_loadUserData();
        $count -= count($this->users);
        return $count;
    }

    public function modifyUser($username, $changes) {
        $this->debug_saml("Called function '_modifyUser()'", __LINE__, __FILE__);
        global $ACT;

        // sanity checks, user must already exist and there must be something to change
        if(($userinfo = $this->getUserData($username)) === false) return false;
        if(!is_array($changes) || !count($changes)) return true;

        // update userinfo with new data
        $newuser = $username;
        foreach($changes as $field => $value) {
            if($field == 'user') {
                $newuser = $value;
                continue;
            }
            $userinfo[$field] = $value;
        }

        $groups   = join(',', array_map('urlencode', $userinfo['grps']));
        $userline = join(':', array($newuser, $userinfo['pass'], $userinfo['name'], $userinfo['mail'], $groups))."\n";

        if(!$this->deleteUsers(array($username))) {
            msg('Unable to modify user data. Please inform the Wiki-Admin', -1);
            return false;
        }

        if(!io_saveFile($this->saml_user_file, $userline, true)) {
            msg('There was an error modifying your user data. You should register again.', -1);
            // FIXME, user has been deleted but not recreated, should force a logout and redirect to login page
            $ACT = 'register';
            return false;
        }

        $this->users[$newuser] = $userinfo;
        return true;
    }

    /**
     * Wrapper for error_log to write debug messages only when debug is enabled in config
     * (adapted from dokuwiki-plugin-authclientcert by Pawel Jasinski <pawel.jasinski@gmail.com>)
     *
     * @param string $message
     * @param int    $line
     * @param string $file
     * @return void
     *
     * @author Dominik Volkamer <dominik.volkamer@fau.de>
     */

    public function debug_saml($message, $line, $file) {
        if(!$this->debug_saml) return;
        error_log($message." [".$file.":".$line."]");
    }
    public function debug_saml_dump($data, $message, $line, $file) {
        $data_dump = print_r($data, true);
        $this->debug_saml($message." ".$data_dump, $line, $file);
    }
}
