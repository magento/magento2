<?php
/**
 * Magento session manager
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Framework\Session\Config\ConfigInterface;

/**
 * Session Manager
 */
class SessionManager implements SessionManagerInterface
{
    /**
     * Default options when a call destroy()
     *
     * Description:
     * - send_expire_cookie: whether or not to send a cookie expiring the current session cookie
     * - clear_storage: whether or not to empty the storage object of any stored values
     *
     * @var array
     */
    protected $defaultDestroyOptions = ['send_expire_cookie' => true, 'clear_storage' => true];

    /**
     * URL host cache
     *
     * @var array
     */
    protected static $urlHostCache = [];

    /**
     * Validator
     *
     * @var \Magento\Framework\Session\ValidatorInterface
     */
    protected $validator;

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * SID resolver
     *
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    protected $sidResolver;

    /**
     * Session config
     *
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    protected $sessionConfig;

    /**
     * Save handler
     *
     * @var \Magento\Framework\Session\SaveHandlerInterface
     */
    protected $saveHandler;

    /**
     * Storage
     *
     * @var \Magento\Framework\Session\StorageInterface
     */
    protected $storage;

    /**
     * Cookie Manager
     *
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param SidResolverInterface $sidResolver
     * @param ConfigInterface $sessionConfig
     * @param SaveHandlerInterface $saveHandler
     * @param ValidatorInterface $validator
     * @param StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        SidResolverInterface $sidResolver,
        ConfigInterface $sessionConfig,
        SaveHandlerInterface $saveHandler,
        ValidatorInterface $validator,
        StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->request = $request;
        $this->sidResolver = $sidResolver;
        $this->sessionConfig = $sessionConfig;
        $this->saveHandler = $saveHandler;
        $this->validator = $validator;
        $this->storage = $storage;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;

        // Enable session.use_only_cookies
        ini_set('session.use_only_cookies', '1');
    }

    /**
     * This method needs to support sessions with APC enabled
     * @return void
     */
    public function writeClose()
    {
        session_write_close();
    }

    /**
     * Storage accessor method
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __call($method, $args)
    {
        if (!in_array(substr($method, 0, 3), ['get', 'set', 'uns', 'has'])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid method %s::%s(%s)', get_class($this), $method, print_r($args, 1))
            );
        }
        $return = call_user_func_array([$this->storage, $method], $args);
        return $return === $this->storage ? $this : $return;
    }

    /**
     * Configure session handler and start session
     *
     * @return $this
     */
    public function start()
    {
        if (!$this->isSessionExists()) {
            \Magento\Framework\Profiler::start('session_start');
            // Need to apply the config options so they can be ready by session_start
            $this->initIniOptions();
            $this->registerSaveHandler();

            // potential custom logic for session id (ex. switching between hosts)
            $this->setSessionId($this->sidResolver->getSid($this));
            session_start();
            $this->validator->validate($this);

            register_shutdown_function([$this, 'writeClose']);

            $this->_addHost();
            \Magento\Framework\Profiler::stop('session_start');
        }
        $this->storage->init(isset($_SESSION) ? $_SESSION : []);
        return $this;
    }

    /**
     * Register save handler
     *
     * @return bool
     */
    protected function registerSaveHandler()
    {
        return session_set_save_handler(
            [$this->saveHandler, 'open'],
            [$this->saveHandler, 'close'],
            [$this->saveHandler, 'read'],
            [$this->saveHandler, 'write'],
            [$this->saveHandler, 'destroy'],
            [$this->saveHandler, 'gc']
        );
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
        $data = $this->storage->getData($key);
        if ($clear && $data) {
            $this->storage->unsetData($key);
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
     * @return $this
     */
    public function setName($name)
    {
        session_name($name);
        return $this;
    }

    /**
     * Destroy/end a session
     *
     * @param  array $options
     * @return void
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
        $this->storage->unsetData();
        return $this;
    }

    /**
     * Retrieve Cookie domain
     *
     * @return string
     */
    public function getCookieDomain()
    {
        return $this->sessionConfig->getCookieDomain();
    }

    /**
     * Retrieve cookie path
     *
     * @return string
     */
    public function getCookiePath()
    {
        return $this->sessionConfig->getCookiePath();
    }

    /**
     * Retrieve cookie lifetime
     *
     * @return int
     */
    public function getCookieLifetime()
    {
        return $this->sessionConfig->getCookieLifetime();
    }

    /**
     * Specify session identifier
     *
     * @param   string|null $sessionId
     * @return  $this
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
        $httpHost = $this->request->getHttpHost();
        if (!$httpHost) {
            return '';
        }

        $urlHostArr = explode('/', $urlHost, 4);
        if (!empty($urlHostArr[2])) {
            $urlHost = $urlHostArr[2];
        }
        $urlPath = empty($urlHostArr[3]) ? '' : $urlHostArr[3];

        if (!isset(self::$urlHostCache[$urlHost])) {
            $urlHostArr = explode(':', $urlHost);
            $urlHost = $urlHostArr[0];
            $sessionId = $httpHost !== $urlHost && !$this->isValidForHost($urlHost) ? $this->getSessionId() : '';
            self::$urlHostCache[$urlHost] = $sessionId;
        }

        return $this->isValidForPath($urlPath) ? self::$urlHostCache[$urlHost] : $this->getSessionId();
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
        return !empty($hosts[$hostArr[0]]);
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
     * @return $this
     */
    protected function _addHost()
    {
        $host = $this->request->getHttpHost();
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
        return isset($_SESSION[self::HOST_KEY]) ? $_SESSION[self::HOST_KEY] : [];
    }

    /**
     * Clean all host names that were registered with session
     *
     * @return $this
     */
    protected function _cleanHosts()
    {
        unset($_SESSION[self::HOST_KEY]);
        return $this;
    }

    /**
     * Renew session id and update session cookie
     *
     * @return $this
     */
    public function regenerateId()
    {
        if (headers_sent()) {
            return $this;
        }
        session_regenerate_id(true);

        if ($this->sessionConfig->getUseCookies()) {
            $this->clearSubDomainSessionCookie();
        }
        return $this;
    }

    /**
     * Expire the session cookie for sub domains
     *
     * @return void
     */
    protected function clearSubDomainSessionCookie()
    {
        foreach (array_keys($this->_getHosts()) as $host) {
            // Delete cookies with the same name for parent domains
            if (strpos($this->sessionConfig->getCookieDomain(), $host) > 0) {
                $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
                $metadata->setPath($this->sessionConfig->getCookiePath());
                $metadata->setDomain($host);
                $metadata->setSecure($this->sessionConfig->getCookieSecure());
                $metadata->setHttpOnly($this->sessionConfig->getCookieHttpOnly());
                $this->cookieManager->deleteCookie($this->getName(), $metadata);
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
        if (!$this->sessionConfig->getUseCookies()) {
            return;
        }

        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $metadata->setPath($this->sessionConfig->getCookiePath());
        $metadata->setDomain($this->sessionConfig->getCookieDomain());
        $metadata->setSecure($this->sessionConfig->getCookieSecure());
        $metadata->setHttpOnly($this->sessionConfig->getCookieHttpOnly());
        $this->cookieManager->deleteCookie($this->getName(), $metadata);
        $this->clearSubDomainSessionCookie();
    }

    /**
     * Performs ini_set for all of the config options so they can be read by session_start
     *
     * @return void
     */
    private function initIniOptions()
    {
        foreach ($this->sessionConfig->getOptions() as $option => $value) {
            $result = ini_set($option, $value);
            if ($result === false) {
                $error = error_get_last();
                throw new \InvalidArgumentException(
                    sprintf('Failed to set ini option "%s" to value "%s". %s', $option, $value, $error['message'])
                );
            }
        }
    }
}
