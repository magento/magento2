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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Core Session Abstract model
 *
 * @category   Mage
 * @package    Mage_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Session_Abstract extends Mage_Core_Model_Session_Abstract_Varien
{
    const XML_PATH_COOKIE_DOMAIN        = 'web/cookie/cookie_domain';
    const XML_PATH_COOKIE_PATH          = 'web/cookie/cookie_path';
    const XML_PATH_COOKIE_LIFETIME      = 'web/cookie/cookie_lifetime';
    const XML_NODE_SESSION_SAVE         = 'global/session_save';
    const XML_NODE_SESSION_SAVE_PATH    = 'global/session_save_path';

    const XML_PATH_USE_REMOTE_ADDR      = 'web/session/use_remote_addr';
    const XML_PATH_USE_HTTP_VIA         = 'web/session/use_http_via';
    const XML_PATH_USE_X_FORWARDED      = 'web/session/use_http_x_forwarded_for';
    const XML_PATH_USE_USER_AGENT       = 'web/session/use_http_user_agent';
    const XML_PATH_USE_FRONTEND_SID     = 'web/session/use_frontend_sid';

    const XML_NODE_USET_AGENT_SKIP      = 'global/session/validation/http_user_agent_skip';
    const XML_PATH_LOG_EXCEPTION_FILE   = 'dev/log/exception_file';

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
     * Init session
     *
     * @param string $namespace
     * @param string $sessionName
     * @return Mage_Core_Model_Session_Abstract
     */
    public function init($namespace, $sessionName=null)
    {
        parent::init($namespace, $sessionName);
        $this->addHost(true);
        return $this;
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
     * Use REMOTE_ADDR in validator key
     *
     * @return bool
     */
    public function useValidateRemoteAddr()
    {
        $use = Mage::getStoreConfig(self::XML_PATH_USE_REMOTE_ADDR);
        if (is_null($use)) {
            return parent::useValidateRemoteAddr();
        }
        return (bool)$use;
    }

    /**
     * Use HTTP_VIA in validator key
     *
     * @return bool
     */
    public function useValidateHttpVia()
    {
        $use = Mage::getStoreConfig(self::XML_PATH_USE_HTTP_VIA);
        if (is_null($use)) {
            return parent::useValidateHttpVia();
        }
        return (bool)$use;
    }

    /**
     * Use HTTP_X_FORWARDED_FOR in validator key
     *
     * @return bool
     */
    public function useValidateHttpXForwardedFor()
    {
        $use = Mage::getStoreConfig(self::XML_PATH_USE_X_FORWARDED);
        if (is_null($use)) {
            return parent::useValidateHttpXForwardedFor();
        }
        return (bool)$use;
    }

    /**
     * Use HTTP_USER_AGENT in validator key
     *
     * @return bool
     */
    public function useValidateHttpUserAgent()
    {
        $use = Mage::getStoreConfig(self::XML_PATH_USE_USER_AGENT);
        if (is_null($use)) {
            return parent::useValidateHttpUserAgent();
        }
        return (bool)$use;
    }

    /**
     * Check whether SID can be used for session initialization
     * Admin area will always have this feature enabled
     *
     * @return bool
     */
    public function useSid()
    {
        return Mage::app()->getStore()->isAdmin() || Mage::getStoreConfig(self::XML_PATH_USE_FRONTEND_SID);
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
     * Adding new nitice message
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
        if (is_null($id) && $this->useSid()) {
            $_queryParam = $this->getSessionIdQueryParam();
            if (isset($_GET[$_queryParam]) && Mage::getSingleton('Mage_Core_Model_Url')->isOwnOriginUrl()) {
                $id = $_GET[$_queryParam];
                /**
                 * No reason use crypt key for session
                 */
//                if ($tryId = Mage::helper('Mage_Core_Helper_Data')->decrypt($_GET[self::SESSION_ID_QUERY_PARAM])) {
//                    $id = $tryId;
//                }
            }
        }

        $this->addHost(true);
        return parent::setSessionId($id);
    }

    /**
     * Get ecrypted session identifuer
     * No reason use crypt key for session id encryption
     * we can use session identifier as is
     *
     * @return string
     */
    public function getEncryptedSessionId()
    {
        if (!self::$_encryptedSessionId) {
//            $helper = Mage::helper('Mage_Core_Helper_Data');
//            if (!$helper) {
//                return $this;
//            }
//            self::$_encryptedSessionId = $helper->encrypt($this->getSessionId());
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
     * If the host was switched but session cookie won't recognize it - add session id to query
     *
     * @param string $urlHost can be host or url
     * @return string {session_id_key}={session_id_encrypted}
     */
    public function getSessionIdForHost($urlHost)
    {
        if ($this->getSkipSessionIdFlag() === true) {
            return '';
        }

        if (!$httpHost = Mage::app()->getFrontController()->getRequest()->getHttpHost()) {
            return '';
        }

        $urlHostArr = explode('/', $urlHost, 4);
        if (!empty($urlHostArr[2])) {
            $urlHost = $urlHostArr[2];
        }

        if (!isset(self::$_urlHostCache[$urlHost])) {
            $urlHostArr = explode(':', $urlHost);
            $urlHost = $urlHostArr[0];

            if ($httpHost !== $urlHost && !$this->isValidForHost($urlHost)) {
                $sessionId = $this->getEncryptedSessionId();
            } else {
                $sessionId = '';
            }
            self::$_urlHostCache[$urlHost] = $sessionId;
        }
        return self::$_urlHostCache[$urlHost];
    }

    /**
     * Check is valid session for hostname
     *
     * @param string $host
     * @return bool
     */
    public function isValidForHost($host)
    {
        $hostArr = explode(':', $host);
        $hosts = $this->getSessionHosts();
        return (!empty($hosts[$hostArr[0]]));
    }

    /**
     * Add hostname to session
     *
     * @param string $host
     * @return Mage_Core_Model_Session_Abstract
     */
    public function addHost($host)
    {
        if ($host === true) {
            if (!$host = Mage::app()->getFrontController()->getRequest()->getHttpHost()) {
                return $this;
            }
        }

        if (!$host) {
            return $this;
        }

        $hosts = $this->getSessionHosts();
        $hosts[$host] = true;
        $this->setSessionHosts($hosts);
        return $this;
    }

    /**
     * Retrieve session hostnames
     *
     * @return array
     */
    public function getSessionHosts()
    {
        return $this->getData('session_hosts');
    }

    /**
     * Retrieve session save method
     *
     * @return string
     */
    public function getSessionSaveMethod()
    {
        if (Mage::isInstalled() && $sessionSave = Mage::getConfig()->getNode(self::XML_NODE_SESSION_SAVE)) {
            return $sessionSave;
        }
        return parent::getSessionSaveMethod();
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
        return parent::getSessionSavePath();
    }

    /**
     * Renew session id and update session cookie
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    public function renewSession()
    {
        $this->getCookie()->delete($this->getSessionName());
        $this->regenerateSessionId();

        $sessionHosts = $this->getSessionHosts();
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
