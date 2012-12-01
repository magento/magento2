<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Core Session Abstract model
 *
 * @category   Mage
 * @package    Mage_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Session_Abstract extends Varien_Object
{
    const VALIDATOR_KEY                         = '_session_validator_data';
    const VALIDATOR_HTTP_USER_AGENT_KEY         = 'http_user_agent';
    const VALIDATOR_HTTP_X_FORVARDED_FOR_KEY    = 'http_x_forwarded_for';
    const VALIDATOR_HTTP_VIA_KEY                = 'http_via';
    const VALIDATOR_REMOTE_ADDR_KEY             = 'remote_addr';

    const XML_PATH_COOKIE_DOMAIN        = 'web/cookie/cookie_domain';
    const XML_PATH_COOKIE_PATH          = 'web/cookie/cookie_path';
    const XML_NODE_SESSION_SAVE         = 'global/session_save';
    const XML_NODE_SESSION_SAVE_PATH    = 'global/session_save_path';

    const XML_PATH_USE_REMOTE_ADDR      = 'web/session/use_remote_addr';
    const XML_PATH_USE_HTTP_VIA         = 'web/session/use_http_via';
    const XML_PATH_USE_X_FORWARDED      = 'web/session/use_http_x_forwarded_for';
    const XML_PATH_USE_USER_AGENT       = 'web/session/use_http_user_agent';
    const XML_PATH_USE_FRONTEND_SID     = 'web/session/use_frontend_sid';

    const XML_NODE_USET_AGENT_SKIP      = 'global/session/validation/http_user_agent_skip';
    const XML_PATH_LOG_EXCEPTION_FILE   = 'dev/log/exception_file';

    const HOST_KEY                      = '_session_hosts';
    const SESSION_ID_QUERY_PARAM        = 'SID';

    /**
     * URL host cache
     *
     * @var array
     */
    protected static $_urlHostCache = array();

    /**
     * Encrypted session id cache
     *
     * @var string
     */
    protected static $_encryptedSessionId;

    /**
     * Skip session id flag
     *
     * @var bool
     */
    protected $_skipSessionIdFlag   = false;

    /**
     * Configure session handler and start session
     *
     * @param string $sessionName
     * @return Mage_Core_Model_Session_Abstract
     */
    public function start($sessionName=null)
    {
        if (isset($_SESSION) && !$this->getSkipEmptySessionCheck()) {
            return $this;
        }

        switch($this->getSessionSaveMethod()) {
            case 'db':
                ini_set('session.save_handler', 'user');
                $sessionResource = Mage::getResourceSingleton('Mage_Core_Model_Resource_Session');
                /* @var $sessionResource Mage_Core_Model_Resource_Session */
                $sessionResource->setSaveHandler();
                break;
            case 'memcache':
                ini_set('session.save_handler', 'memcache');
                session_save_path($this->getSessionSavePath());
                break;
            case 'memcached':
                ini_set('session.save_handler', 'memcached');
                session_save_path($this->getSessionSavePath());
                break;
            case 'eaccelerator':
                ini_set('session.save_handler', 'eaccelerator');
                break;
            default:
                session_module_name($this->getSessionSaveMethod());
                if (is_writable($this->getSessionSavePath())) {
                    session_save_path($this->getSessionSavePath());
                }
                break;
        }
        $cookie = $this->getCookie();

        // session cookie params
        $cookieParams = array(
            'lifetime' => 0, // 0 is browser session lifetime
            'path'     => $cookie->getPath(),
            'domain'   => $cookie->getConfigDomain(),
            'secure'   => $cookie->isSecure(),
            'httponly' => $cookie->getHttponly()
        );

        if (!$cookieParams['httponly']) {
            unset($cookieParams['httponly']);
            if (!$cookieParams['secure']) {
                unset($cookieParams['secure']);
                if (!$cookieParams['domain']) {
                    unset($cookieParams['domain']);
                }
            }
        }

        if (isset($cookieParams['domain'])) {
            $cookieParams['domain'] = $cookie->getDomain();
        }

        call_user_func_array('session_set_cookie_params', $cookieParams);

        if (!empty($sessionName)) {
            $this->setSessionName($sessionName);
        }

        // potential custom logic for session id (ex. switching between hosts)
        $this->setSessionId();

        Magento_Profiler::start('session_start');
        $sessionCacheLimiter = Mage::getConfig()->getNode('global/session_cache_limiter');
        if ($sessionCacheLimiter) {
            session_cache_limiter((string)$sessionCacheLimiter);
        }

        session_start();

        Magento_Profiler::stop('session_start');

        return $this;
    }

    /**
     * Retrieve cookie object
     *
     * @return Mage_Core_Model_Cookie
     */
    public function getCookie()
    {
        return Mage::getSingleton('Mage_Core_Model_Cookie');
    }

    /**
     * Init session with namespace
     *
     * @param string $namespace
     * @param string $sessionName
     * @return Mage_Core_Model_Session_Abstract
     */
    public function init($namespace, $sessionName=null)
    {
        if (!isset($_SESSION)) {
            $this->start($sessionName);
        }
        if (!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = array();
        }

        $this->_data = &$_SESSION[$namespace];

        $this->validate();
        $this->_addHost();
        return $this;
    }

    /**
     * Additional get data with clear mode
     *
     * @param string $key
     * @param bool $clear
     * @return mixed
     */
    public function getData($key='', $clear = false)
    {
        $data = parent::getData($key);
        if ($clear && isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
        return $data;
    }

    /**
     * Retrieve session Id
     *
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * Retrieve session name
     *
     * @return string
     */
    public function getSessionName()
    {
        return session_name();
    }

    /**
     * Set session name
     *
     * @param string $name
     * @return Mage_Core_Model_Session_Abstract
     */
    public function setSessionName($name)
    {
        session_name($name);
        return $this;
    }

    /**
     * Unset all data
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    public function unsetAll()
    {
        $this->unsetData();
        return $this;
    }

    /**
     * Alias for unsetAll
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    public function clear()
    {
        return $this->unsetAll();
    }

    /**
     * Validate session
     *
     * @param string $namespace
     * @return Mage_Core_Model_Session_Abstract
     */
    public function validate()
    {
        if (!isset($_SESSION[self::VALIDATOR_KEY])) {
            $_SESSION[self::VALIDATOR_KEY] = $this->_getSessionEnvironment();
        } else {
            if (!$this->_validate()) {
                $this->getCookie()->delete(session_name());
                // throw core session exception
                throw new Mage_Core_Model_Session_Exception('');
            }
        }

        return $this;
    }

    /**
     * Validate data
     *
     * @return bool
     */
    protected function _validate()
    {
        $sessionData = $_SESSION[self::VALIDATOR_KEY];
        $validatorData = $this->_getSessionEnvironment();

        if (Mage::getStoreConfig(self::XML_PATH_USE_REMOTE_ADDR)
                && $sessionData[self::VALIDATOR_REMOTE_ADDR_KEY] != $validatorData[self::VALIDATOR_REMOTE_ADDR_KEY]) {
            return false;
        }
        if (Mage::getStoreConfig(self::XML_PATH_USE_HTTP_VIA)
                && $sessionData[self::VALIDATOR_HTTP_VIA_KEY] != $validatorData[self::VALIDATOR_HTTP_VIA_KEY]) {
            return false;
        }

        $sessionValidateHttpXForwardedForKey = $sessionData[self::VALIDATOR_HTTP_X_FORVARDED_FOR_KEY];
        $validatorValidateHttpXForwardedForKey = $validatorData[self::VALIDATOR_HTTP_X_FORVARDED_FOR_KEY];
        if (Mage::getStoreConfig(self::XML_PATH_USE_X_FORWARDED)
            && $sessionValidateHttpXForwardedForKey != $validatorValidateHttpXForwardedForKey ) {
            return false;
        }
        if (Mage::getStoreConfig(self::XML_PATH_USE_USER_AGENT)
            && $sessionData[self::VALIDATOR_HTTP_USER_AGENT_KEY] != $validatorData[self::VALIDATOR_HTTP_USER_AGENT_KEY]
        ) {
            $userAgentValidated = $this->getValidateHttpUserAgentSkip();
            foreach ($userAgentValidated as $agent) {
                if (preg_match('/' . $agent . '/iu', $validatorData[self::VALIDATOR_HTTP_USER_AGENT_KEY])) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Prepare session environment data for validation
     *
     * @return array
     */
    protected function _getSessionEnvironment()
    {
        $parts = array(
            self::VALIDATOR_REMOTE_ADDR_KEY             => '',
            self::VALIDATOR_HTTP_VIA_KEY                => '',
            self::VALIDATOR_HTTP_X_FORVARDED_FOR_KEY    => '',
            self::VALIDATOR_HTTP_USER_AGENT_KEY         => ''
        );

        // collect ip data
        if (Mage::helper('Mage_Core_Helper_Http')->getRemoteAddr()) {
            $parts[self::VALIDATOR_REMOTE_ADDR_KEY] = Mage::helper('Mage_Core_Helper_Http')->getRemoteAddr();
        }
        if (isset($_ENV['HTTP_VIA'])) {
            $parts[self::VALIDATOR_HTTP_VIA_KEY] = (string)$_ENV['HTTP_VIA'];
        }
        if (isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
            $parts[self::VALIDATOR_HTTP_X_FORVARDED_FOR_KEY] = (string)$_ENV['HTTP_X_FORWARDED_FOR'];
        }

        // collect user agent data
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $parts[self::VALIDATOR_HTTP_USER_AGENT_KEY] = (string)$_SERVER['HTTP_USER_AGENT'];
        }

        return $parts;
    }

    /**
     * Retrieve Cookie domain
     *
     * @return string
     */
    public function getCookieDomain()
    {
        return $this->getCookie()->getDomain();
    }

    /**
     * Retrieve cookie path
     *
     * @return string
     */
    public function getCookiePath()
    {
        return $this->getCookie()->getPath();
    }

    /**
     * Retrieve cookie lifetime
     *
     * @return int
     */
    public function getCookieLifetime()
    {
        return $this->getCookie()->getLifetime();
    }

    /**
     * Retrieve skip User Agent validation strings (Flash etc)
     *
     * @return array
     */
    public function getValidateHttpUserAgentSkip()
    {
        $userAgents = array();
        $skip = Mage::getConfig()->getNode(self::XML_NODE_USET_AGENT_SKIP);
        foreach ($skip->children() as $userAgent) {
            $userAgents[] = (string)$userAgent;
        }
        return $userAgents;
    }

    /**
     * Retrieve messages from session
     *
     * @param   bool $clear
     * @return  Mage_Core_Model_Message_Collection
     */
    public function getMessages($clear=false)
    {
        if (!$this->getData('messages')) {
            $this->setMessages(Mage::getModel('Mage_Core_Model_Message_Collection'));
        }

        if ($clear) {
            $messages = clone $this->getData('messages');
            $this->getData('messages')->clear();
            Mage::dispatchEvent('core_session_abstract_clear_messages');
            return $messages;
        }
        return $this->getData('messages');
    }

    /**
     * Not Mage exception handling
     *
     * @param   Exception $exception
     * @param   string $alternativeText
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addException(Exception $exception, $alternativeText)
    {
        // log exception to exceptions log
        $message = sprintf('Exception message: %s%sTrace: %s',
            $exception->getMessage(),
            "\n",
            $exception->getTraceAsString());
        $file    = Mage::getStoreConfig(self::XML_PATH_LOG_EXCEPTION_FILE);
        Mage::log($message, Zend_Log::DEBUG, $file);

        $this->addMessage(Mage::getSingleton('Mage_Core_Model_Message')->error($alternativeText));
        return $this;
    }

    /**
     * Adding new message to message collection
     *
     * @param   Mage_Core_Model_Message_Abstract $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addMessage(Mage_Core_Model_Message_Abstract $message)
    {
        $this->getMessages()->add($message);
        Mage::dispatchEvent('core_session_abstract_add_message');
        return $this;
    }

    /**
     * Adding new error message
     *
     * @param   string $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addError($message)
    {
        $this->addMessage(Mage::getSingleton('Mage_Core_Model_Message')->error($message));
        return $this;
    }

    /**
     * Adding new warning message
     *
     * @param   string $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addWarning($message)
    {
        $this->addMessage(Mage::getSingleton('Mage_Core_Model_Message')->warning($message));
        return $this;
    }

    /**
     * Adding new notice message
     *
     * @param   string $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addNotice($message)
    {
        $this->addMessage(Mage::getSingleton('Mage_Core_Model_Message')->notice($message));
        return $this;
    }

    /**
     * Adding new success message
     *
     * @param   string $message
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addSuccess($message)
    {
        $this->addMessage(Mage::getSingleton('Mage_Core_Model_Message')->success($message));
        return $this;
    }

    /**
     * Adding messages array to message collection
     *
     * @param   array $messages
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addMessages($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $this->addMessage($message);
            }
        }
        return $this;
    }

    /**
     * Adds messages array to message collection, but doesn't add duplicates to it
     *
     * @param   array|string|Mage_Core_Model_Message_Abstract $messages
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function addUniqueMessages($messages)
    {
        if (!is_array($messages)) {
            $messages = array($messages);
        }
        if (!$messages) {
            return $this;
        }

        $messagesAlready = array();
        $items = $this->getMessages()->getItems();
        foreach ($items as $item) {
            if ($item instanceof Mage_Core_Model_Message_Abstract) {
                $text = $item->getText();
            } else if (is_string($item)) {
                $text = $item;
            } else {
                continue; // Some unknown object, do not put it in already existing messages
            }
            $messagesAlready[$text] = true;
        }

        foreach ($messages as $message) {
            if ($message instanceof Mage_Core_Model_Message_Abstract) {
                $text = $message->getText();
            } else if (is_string($message)) {
                $text = $message;
            } else {
                $text = null; // Some unknown object, add it anyway
            }

            // Check for duplication
            if ($text !== null) {
                if (isset($messagesAlready[$text])) {
                    continue;
                }
                $messagesAlready[$text] = true;
            }
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * Specify session identifier
     *
     * @param   string|null $id
     * @return  Mage_Core_Model_Session_Abstract
     */
    public function setSessionId($id=null)
    {

        if (is_null($id)
            && (Mage::app()->getStore()->isAdmin() || Mage::getStoreConfig(self::XML_PATH_USE_FRONTEND_SID))) {
            $_queryParam = $this->getSessionIdQueryParam();
            if (isset($_GET[$_queryParam]) && Mage::getSingleton('Mage_Core_Model_Url')->isOwnOriginUrl()) {
                $id = $_GET[$_queryParam];
            }
        }

        $this->_addHost();
        if (!is_null($id) && preg_match('#^[0-9a-zA-Z,-]+$#', $id)) {
            session_id($id);
        }
        return $this;
    }

    /**
     * Get encrypted session identifier.
     * No reason use crypt key for session id encryption, we can use session identifier as is.
     *
     * @return string
     */
    public function getEncryptedSessionId()
    {
        if (!self::$_encryptedSessionId) {
            self::$_encryptedSessionId = $this->getSessionId();
        }
        return self::$_encryptedSessionId;
    }

    public function getSessionIdQueryParam()
    {
        $_sessionName = $this->getSessionName();
        if ($_sessionName && $queryParam = (string)Mage::getConfig()->getNode($_sessionName . '/session/query_param')) {
            return $queryParam;
        }
        return self::SESSION_ID_QUERY_PARAM;
    }

    /**
     * Set skip flag if need skip generating of _GET session_id_key param
     *
     * @param bool $flag
     * @return Mage_Core_Model_Session_Abstract
     */
    public function setSkipSessionIdFlag($flag)
    {
        $this->_skipSessionIdFlag = $flag;
        return $this;
    }

    /**
     * Retrieve session id skip flag
     *
     * @return bool
     */
    public function getSkipSessionIdFlag()
    {
        return $this->_skipSessionIdFlag;
    }

    /**
     * If session cookie is not applicable due to host or path mismatch - add session id to query
     *
     * @param string $urlHost can be host or url
     * @return string {session_id_key}={session_id_encrypted}
     */
    public function getSessionIdForHost($urlHost)
    {
        if ($this->getSkipSessionIdFlag() === true) {
            return '';
        }

        $httpHost = Mage::app()->getRequest()->getHttpHost();
        if (!$httpHost) {
            return '';
        }

        $urlHostArr = explode('/', $urlHost, 4);
        if (!empty($urlHostArr[2])) {
            $urlHost = $urlHostArr[2];
        }
        $urlPath = empty($urlHostArr[3]) ? '' : $urlHostArr[3];

        if (!isset(self::$_urlHostCache[$urlHost])) {
            $urlHostArr = explode(':', $urlHost);
            $urlHost = $urlHostArr[0];
            $sessionId = $httpHost !== $urlHost && !$this->isValidForHost($urlHost)
                ? $this->getEncryptedSessionId() : '';
            self::$_urlHostCache[$urlHost] = $sessionId;
        }

        return Mage::app()->getStore()->isAdmin() || $this->isValidForPath($urlPath) ? self::$_urlHostCache[$urlHost]
            : $this->getEncryptedSessionId();
    }

    /**
     * Check if session is valid for given hostname
     *
     * @param string $host
     * @return bool
     */
    public function isValidForHost($host)
    {
        $hostArr = explode(':', $host);
        $hosts = $this->_getHosts();
        return (!empty($hosts[$hostArr[0]]));
    }

    /**
     * Check if session is valid for given path
     *
     * @param string $path
     * @return bool
     */
    public function isValidForPath($path)
    {
        $cookiePath = trim($this->getCookiePath(), '/') . '/';
        if ($cookiePath == '/') {
            return true;
        }

        $urlPath = trim($path, '/') . '/';

        return strpos($urlPath, $cookiePath) === 0;
    }

    /**
     * Register request host name as used with session
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _addHost()
    {
        $host = Mage::app()->getRequest()->getHttpHost();
        if (!$host) {
            return $this;
        }

        $hosts = $this->_getHosts();
        $hosts[$host] = true;
        $_SESSION[self::HOST_KEY] = $hosts;
        return $this;
    }

    /**
     * Get all host names where session was used
     *
     * @return array
     */
    protected function _getHosts()
    {
        return isset($_SESSION[self::HOST_KEY]) ? $_SESSION[self::HOST_KEY] : array();
    }

    /**
     * Clean all host names that were registered with session
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _cleanHosts()
    {
        unset($_SESSION[self::HOST_KEY]);
        return $this;
    }

    /**
     * Retrieve session save method
     *
     * @return string
     */
    public function getSessionSaveMethod()
    {
        if (Mage::isInstalled() && $sessionSave = Mage::getConfig()->getNode(self::XML_NODE_SESSION_SAVE)) {
            return (string) $sessionSave;
        }
        return 'files';
    }

    /**
     * Get session save path
     *
     * @return string
     */
    public function getSessionSavePath()
    {
        if (Mage::isInstalled() && $sessionSavePath = Mage::getConfig()->getNode(self::XML_NODE_SESSION_SAVE_PATH)) {
            return $sessionSavePath;
        }
        return Mage::getBaseDir('session');
    }

    /**
     * Renew session id and update session cookie
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    public function renewSession()
    {
        if (headers_sent()) {
            Mage::log('Can not regenerate session id because HTTP headers already sent.');
            return $this;
        }
        session_regenerate_id(true);

        $sessionHosts = $this->_getHosts();
        $currentCookieDomain = $this->getCookie()->getDomain();
        if (is_array($sessionHosts)) {
            foreach (array_keys($sessionHosts) as $host) {
                // Delete cookies with the same name for parent domains
                if (strpos($currentCookieDomain, $host) > 0) {
                    $this->getCookie()->delete($this->getSessionName(), null, $host);
                }
            }
        }

        return $this;
    }
}
