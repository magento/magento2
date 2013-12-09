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
    /**
     * Configuration path to log exception file
     */
    const XML_PATH_LOG_EXCEPTION_FILE = 'dev/log/exception_file';

    /**
     * Session key for list of hosts
     */
    const HOST_KEY = '_session_hosts';

    /**
     * Default options when a call destroy()
     *
     * - send_expire_cookie: whether or not to send a cookie expiring the current session cookie
     * - clear_storage: whether or not to empty the storage object of any stored values
     *
     * @var array
     */
    protected $defaultDestroyOptions = array(
        'send_expire_cookie' => true,
        'clear_storage'      => true,
    );

    /**
     * URL host cache
     *
     * @var array
     */
    protected static $_urlHostCache = array();

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

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
     * Core message
     *
     * @var \Magento\Message\Factory
     */
    protected $messageFactory;

    /**
     * Core message collection factory
     *
     * @var \Magento\Message\CollectionFactory
     */
    protected $messagesFactory;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * @var \Magento\Session\Config\ConfigInterface
     */
    protected $_sessionConfig;

    /**
     * @param \Magento\Core\Model\Session\Context $context
     * @param \Magento\Session\SidResolverInterface $sidResolver
     * @param \Magento\Session\Config\ConfigInterface $sessionConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Session\Context $context,
        \Magento\Session\SidResolverInterface $sidResolver,
        \Magento\Session\Config\ConfigInterface $sessionConfig,
        array $data = array()
    ) {
        $this->_validator = $context->getValidator();
        $this->_eventManager = $context->getEventManager();
        $this->_logger = $context->getLogger();
        $this->_coreStoreConfig = $context->getStoreConfig();
        $this->_saveMethod = $this->_saveMethod ?: $context->getSaveMethod();
        $this->messagesFactory = $context->getMessagesFactory();
        $this->messageFactory = $context->getMessageFactory();
        $this->_request = $context->getRequest();
        $this->_appState = $context->getAppState();
        $this->_storeManager = $context->getStoreManager();
        $this->_sidResolver = $sidResolver;
        $this->_sessionConfig = $sessionConfig;
        parent::__construct($data);
    }

    /**
     * Init session handler
     */
    protected function _initSessionHandler()
    {
        \Magento\Profiler::start('session_start');
        switch($this->getSessionSaveMethod()) {
            case 'db':
                ini_set('session.save_handler', 'user');
                /* @var $sessionResource \Magento\Core\Model\Resource\Session */
                $sessionResource = \Magento\App\ObjectManager::getInstance()
                    ->get('Magento\Core\Model\Resource\Session');
                $sessionResource->setSaveHandler();
                break;
            case 'memcache':
                ini_set('session.save_handler', 'memcache');
                break;
            case 'memcached':
                ini_set('session.save_handler', 'memcached');
                break;
            case 'eaccelerator':
                ini_set('session.save_handler', 'eaccelerator');
                break;
            default:
                session_module_name($this->getSessionSaveMethod());
                break;
        }

        // potential custom logic for session id (ex. switching between hosts)
        $this->setSessionId($this->_sidResolver->getSid($this));

        session_start();
        register_shutdown_function(array($this, 'writeClose'));

        \Magento\Profiler::stop('session_start');
    }

    /**
     * This method needs to support sessions with APC enabled
     */
    public function writeClose()
    {
        session_write_close();
    }

    /**
     * Configure session handler and start session
     *
     * @param string $namespace
     * @param string $sessionName
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function start($namespace = 'default', $sessionName = null)
    {
        if (!$this->isSessionExists()) {
            if (!empty($sessionName)) {
                $this->setSessionName($sessionName);
            }
            $this->_initSessionHandler();
            $this->_validator->validate($this);
            $this->_addHost();
        }

        if (!isset($_SESSION[$namespace])) {
            $_SESSION[$namespace] = array();
        }
        $this->_data = &$_SESSION[$namespace];
        return $this;
    }

    /**
     * Does a session exist
     *
     * @return bool
     */
    public function isSessionExists()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            return false;
        }
        return true;
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
    public function getName()
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
     * Destroy/end a session
     *
     * @param  array $options
     */
    public function destroy(array $options = null)
    {
        if (null === $options) {
            $options = $this->defaultDestroyOptions;
        } else {
            $options = array_merge($this->defaultDestroyOptions, $options);
        }

        if ($options['clear_storage']) {
            $this->clearStorage();
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        session_destroy();
        if ($options['send_expire_cookie']) {
            $this->expireSessionCookie();
        }
    }

    /**
     * Unset all session data
     *
     * @return $this
     */
    public function clearStorage()
    {
        $this->unsetData();
        return $this;
    }

    /**
     * Retrieve Cookie domain
     *
     * @return string
     */
    public function getCookieDomain()
    {
        return $this->_sessionConfig->getCookieDomain();
    }

    /**
     * Retrieve cookie path
     *
     * @return string
     */
    public function getCookiePath()
    {
        return $this->_sessionConfig->getCookiePath();
    }

    /**
     * Retrieve cookie lifetime
     *
     * @return int
     */
    public function getCookieLifetime()
    {
        return $this->_sessionConfig->getCookieLifetime();
    }

    /**
     * Retrieve messages from session
     *
     * @param   bool $clear
     * @return  \Magento\Message\Collection
     */
    public function getMessages($clear = false)
    {
        if (!$this->getData('messages')) {
            $this->setMessages($this->messagesFactory->create());
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

        $this->addMessage($this->messageFactory->error($alternativeText));
        return $this;
    }

    /**
     * Adding new message to message collection
     *
     * @param   \Magento\Message\AbstractMessage $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addMessage(\Magento\Message\AbstractMessage $message)
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
        $this->addMessage($this->messageFactory->error($message));
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
        $this->addMessage($this->messageFactory->warning($message));
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
        $this->addMessage($this->messageFactory->notice($message));
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
        $this->addMessage($this->messageFactory->success($message));
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
     * @param   array|string|\Magento\Message\AbstractMessage $messages
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
            if ($item instanceof \Magento\Message\AbstractMessage) {
                $text = $item->getText();
            } elseif (is_string($item)) {
                $text = $item;
            } else {
                continue; // Some unknown object, do not put it in already existing messages
            }
            $messagesAlready[$text] = true;
        }

        foreach ($messages as $message) {
            if ($message instanceof \Magento\Message\AbstractMessage) {
                $text = $message->getText();
            } elseif (is_string($message)) {
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
     * @param   string|null $sessionId
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function setSessionId($sessionId)
    {
        $this->_addHost();
        if (!is_null($sessionId) && preg_match('#^[0-9a-zA-Z,-]+$#', $sessionId)) {
            session_id($sessionId);
        }
        return $this;
    }

    /**
     * If session cookie is not applicable due to host or path mismatch - add session id to query
     *
     * @param string $urlHost can be host or url
     * @return string {session_id_key}={session_id_encrypted}
     */
    public function getSessionIdForHost($urlHost)
    {
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
                ? $this->getSessionId() : '';
            self::$_urlHostCache[$urlHost] = $sessionId;
        }

        return $this->isValidForPath($urlPath) ? self::$_urlHostCache[$urlHost] : $this->getSessionId();
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
     * Renew session id and update session cookie
     *
     * @param bool $deleteOldSession
     * @return \Magento\Core\Model\Session\AbstractSession
     * @throws \LogicException
     */
    public function regenerateId($deleteOldSession = true)
    {
        if ($this->isSessionExists()) {
            return $this;
        }
        session_regenerate_id($deleteOldSession);

        if ($this->_sessionConfig->getUseCookies()) {
            $this->clearSubDomainSessionCookie();
        }
        return $this;
    }

    /**
     * Expire the session cookie for sub domains
     */
    protected function clearSubDomainSessionCookie()
    {
        foreach (array_keys($this->_getHosts()) as $host) {
            // Delete cookies with the same name for parent domains
            if (strpos($this->_sessionConfig->getCookieDomain(), $host) > 0) {
                setcookie(
                    $this->getName(),
                    '',
                    0,
                    $this->_sessionConfig->getCookiePath(),
                    $host,
                    $this->_sessionConfig->getCookieSecure(),
                    $this->_sessionConfig->getCookieHttpOnly()
                );
            }
        }
    }

    /**
     * Expire the session cookie
     *
     * Sends a session cookie with no value, and with an expiry in the past.
     *
     * @return void
     */
    public function expireSessionCookie()
    {
        if (!$this->_sessionConfig->getUseCookies()) {
            return;
        }

        setcookie(
            $this->getName(),                 // session name
            '',                               // value
            0,                                // TTL for cookie
            $this->_sessionConfig->getCookiePath(),
            $this->_sessionConfig->getCookieDomain(),
            $this->_sessionConfig->getCookieSecure(),
            $this->_sessionConfig->getCookieHttpOnly()
        );
        $this->clearSubDomainSessionCookie();
    }
}
