<?php
/**
 * Core Session Abstract model
 *
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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Session;

class AbstractSession extends \Magento\Object
{
    const XML_PATH_COOKIE_DOMAIN        = 'web/cookie/cookie_domain';
    const XML_PATH_COOKIE_PATH          = 'web/cookie/cookie_path';

    const PARAM_SESSION_SAVE_METHOD     = 'session_save';
    const PARAM_SESSION_SAVE_PATH       = 'session_save_path';
    const PARAM_SESSION_CACHE_LIMITER   = 'session_cache_limiter';

    const XML_PATH_USE_FRONTEND_SID     = 'web/session/use_frontend_sid';

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
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * Core http
     *
     * @var \Magento\Core\Helper\Http
     */
    protected $_coreHttp = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Core\Model\Session\Validator
     */
    protected $_validator;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var string
     */
    protected $_saveMethod;

    /**
     * Core cookie
     *
     * @var \Magento\Core\Model\Cookie
     */
    protected $_cookie;

    /**
     * Core message
     *
     * @var \Magento\Core\Model\Message
     */
    protected $_message;

    /**
     * Core message collection factory
     *
     * @var \Magento\Core\Model\Message\CollectionFactory
     */
    protected $_messageFactory;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * @var \Magento\Core\Model\Url
     */
    protected $_url;

    /**
     * @var string
     */
    protected $_savePath;

    /**
     * @var string
     */
    protected $_cacheLimiter;

    /**
     * @var array
     */
    protected $_sidNameMap;

    /**
     * @param \Magento\Core\Model\Session\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Session\Context $context,
        array $data = array()
    ) {
        $this->_validator = $context->getValidator();
        $this->_eventManager = $context->getEventManager();
        $this->_coreHttp = $context->getHttpHelper();
        $this->_logger = $context->getLogger();
        $this->_coreStoreConfig = $context->getStoreConfig();
        $this->_savePath = $this->_savePath ?: $context->getSavePath();
        $this->_saveMethod = $this->_saveMethod ?: $context->getSaveMethod();
        $this->_cacheLimiter = $this->_cacheLimiter ?: $context->getCacheLimiter();
        $this->_sidNameMap = $context->getSidMap();
        $this->_messageFactory = $context->getMessageFactory();
        $this->_message = $context->getMessage();
        $this->_cookie = $context->getCookie();
        $this->_request = $context->getRequest();
        $this->_appState = $context->getAppState();
        $this->_storeManager = $context->getStoreManager();
        $this->_dir = $context->getDir();
        $this->_url = $context->getUrl();
        parent::__construct($data);
    }

    /**
     * This method needs to support sessions with APC enabled
     */
    public function __destruct()
    {
        session_write_close();
    }

    /**
     * Configure session handler and start session
     *
     * @param string $sessionName
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function start($sessionName = null)
    {
        if (isset($_SESSION) && !$this->getSkipEmptySessionCheck()) {
            return $this;
        }

        switch($this->getSessionSaveMethod()) {
            case 'db':
                ini_set('session.save_handler', 'user');
                /* @var $sessionResource \Magento\Core\Model\Resource\Session */
                $sessionResource = \Magento\Core\Model\ObjectManager::getInstance()
                    ->get('Magento\Core\Model\Resource\Session');
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

        \Magento\Profiler::start('session_start');

        if ($this->_cacheLimiter) {
            session_cache_limiter($this->_cacheLimiter);
        }

        session_start();

        \Magento\Profiler::stop('session_start');

        return $this;
    }

    /**
     * Retrieve cookie object
     *
     * @return \Magento\Core\Model\Cookie
     */
    public function getCookie()
    {
        return $this->_cookie;
    }

    /**
     * Init session with namespace
     *
     * @param string $namespace
     * @param string $sessionName
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function init($namespace, $sessionName = null)
    {
        if (!isset($_SESSION)) {
            $this->start($sessionName);
        }
        if (!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = array();
        }

        $this->_data = &$_SESSION[$namespace];

        $this->_validator->validate($this);
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
    public function getData($key = '', $clear = false)
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
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function setSessionName($name)
    {
        session_name($name);
        return $this;
    }

    /**
     * Unset all data
     *
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function unsetAll()
    {
        $this->unsetData();
        return $this;
    }

    /**
     * Alias for unsetAll
     *
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function clear()
    {
        return $this->unsetAll();
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
     * Retrieve messages from session
     *
     * @param   bool $clear
     * @return  \Magento\Core\Model\Message\Collection
     */
    public function getMessages($clear = false)
    {
        if (!$this->getData('messages')) {
            $this->setMessages($this->_messageFactory->create());
        }

        if ($clear) {
            $messages = clone $this->getData('messages');
            $this->getData('messages')->clear();
            $this->_eventManager->dispatch('core_session_abstract_clear_messages');
            return $messages;
        }
        return $this->getData('messages');
    }

    /**
     * Not Magento exception handling
     *
     * @param   \Exception $exception
     * @param   string $alternativeText
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addException(\Exception $exception, $alternativeText)
    {
        // log exception to exceptions log
        $message = sprintf('Exception message: %s%sTrace: %s',
            $exception->getMessage(),
            "\n",
            $exception->getTraceAsString());
        $file = $this->_coreStoreConfig->getConfig(self::XML_PATH_LOG_EXCEPTION_FILE);
        $this->_logger->logFile($message, \Zend_Log::DEBUG, $file);

        $this->addMessage($this->_message->error($alternativeText));
        return $this;
    }

    /**
     * Adding new message to message collection
     *
     * @param   \Magento\Core\Model\Message\AbstractMessage $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addMessage(\Magento\Core\Model\Message\AbstractMessage $message)
    {
        $this->getMessages()->add($message);
        $this->_eventManager->dispatch('core_session_abstract_add_message');
        return $this;
    }

    /**
     * Adding new error message
     *
     * @param   string $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addError($message)
    {
        $this->addMessage($this->_message->error($message));
        return $this;
    }

    /**
     * Adding new warning message
     *
     * @param   string $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addWarning($message)
    {
        $this->addMessage($this->_message->warning($message));
        return $this;
    }

    /**
     * Adding new notice message
     *
     * @param   string $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addNotice($message)
    {
        $this->addMessage($this->_message->notice($message));
        return $this;
    }

    /**
     * Adding new success message
     *
     * @param   string $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addSuccess($message)
    {
        $this->addMessage($this->_message->success($message));
        return $this;
    }

    /**
     * Adding messages array to message collection
     *
     * @param   array $messages
     * @return  \Magento\Core\Model\Session\AbstractSession
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
     * @param   array|string|\Magento\Core\Model\Message\AbstractMessage $messages
     * @return  \Magento\Core\Model\Session\AbstractSession
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
            if ($item instanceof \Magento\Core\Model\Message\AbstractMessage) {
                $text = $item->getText();
            } else if (is_string($item)) {
                $text = $item;
            } else {
                continue; // Some unknown object, do not put it in already existing messages
            }
            $messagesAlready[$text] = true;
        }

        foreach ($messages as $message) {
            if ($message instanceof \Magento\Core\Model\Message\AbstractMessage) {
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
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function setSessionId($id = null)
    {

        if (null === $id
            && ($this->_storeManager->getStore()->isAdmin() || $this->_coreStoreConfig->getConfig(self::XML_PATH_USE_FRONTEND_SID))
        ) {
            $_queryParam = $this->getSessionIdQueryParam();
            if (isset($_GET[$_queryParam]) && $this->_url->isOwnOriginUrl()) {
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

    /**
     * Get session id query param
     *
     * @return string
     */
    public function getSessionIdQueryParam()
    {
        $sessionName = $this->getSessionName();
        if ($sessionName && isset($this->_sidNameMap[$sessionName])) {
            return $this->_sidNameMap[$sessionName];
        }
        return self::SESSION_ID_QUERY_PARAM;
    }

    /**
     * Set skip flag if need skip generating of _GET session_id_key param
     *
     * @param bool $flag
     * @return \Magento\Core\Model\Session\AbstractSession
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

        $httpHost = $this->_request->getHttpHost();
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

        return $this->_storeManager->getStore()->isAdmin() || $this->isValidForPath($urlPath)
            ? self::$_urlHostCache[$urlHost]
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
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    protected function _addHost()
    {
        $host = $this->_request->getHttpHost();
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
     * @return \Magento\Core\Model\Session\AbstractSession
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
        if ($this->_appState->isInstalled() && $this->_saveMethod) {
            return $this->_saveMethod;
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
        if ($this->_appState->isInstalled() && $this->_savePath) {
            return $this->_savePath;
        }
        return $this->_dir->getDir('session');
    }

    /**
     * Renew session id and update session cookie
     *
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function renewSession()
    {
        if (headers_sent()) {
            $this->_logger->log('Can not regenerate session id because HTTP headers already sent.');
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
