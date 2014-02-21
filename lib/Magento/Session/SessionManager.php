<?php
/**
 * Magento session manager
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Session;

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
    protected $defaultDestroyOptions = array(
        'send_expire_cookie' => true,
        'clear_storage'      => true,
    );

    /**
     * URL host cache
     *
     * @var array
     */
    protected static $urlHostCache = array();

    /**
     * Validator
     *
     * @var \Magento\Session\ValidatorInterface
     */
    protected $validator;

    /**
     * Request
     *
     * @var \Magento\App\RequestInterface
     */
    protected $request;

    /**
     * SID resolver
     *
     * @var \Magento\Session\SidResolverInterface
     */
    protected $sidResolver;

    /**
     * Session config
     *
     * @var \Magento\Session\Config\ConfigInterface
     */
    protected $sessionConfig;

    /**
     * Save handler
     *
     * @var \Magento\Session\SaveHandlerInterface
     */
    protected $saveHandler;

    /**
     * Storage
     *
     * @var \Magento\Session\StorageInterface
     */
    protected $storage;

    /**
     * Constructor
     *
     * @param \Magento\App\RequestInterface $request
     * @param SidResolverInterface $sidResolver
     * @param Config\ConfigInterface $sessionConfig
     * @param SaveHandlerInterface $saveHandler
     * @param ValidatorInterface $validator
     * @param StorageInterface $storage
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        SidResolverInterface $sidResolver,
        Config\ConfigInterface $sessionConfig,
        SaveHandlerInterface $saveHandler,
        ValidatorInterface $validator,
        StorageInterface $storage
    ) {
        $this->request = $request;
        $this->sidResolver = $sidResolver;
        $this->sessionConfig = $sessionConfig;
        $this->saveHandler = $saveHandler;
        $this->validator = $validator;
        $this->storage = $storage;
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
        if (!in_array(substr($method, 0, 3), array('get', 'set', 'uns', 'has'))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid method %s::%s(%s)', get_class($this), $method, print_r($args, 1))
            );
        }
        $return = call_user_func_array(array($this->storage, $method), $args);
        return $return === $this->storage ? $this : $return;
    }

    /**
     * Configure session handler and start session
     *
     * @param string $sessionName
     * @return $this
     */
    public function start($sessionName = null)
    {
        if (!$this->isSessionExists()) {
            \Magento\Profiler::start('session_start');
            if (!empty($sessionName)) {
                $this->setName($sessionName);
            }
            $this->registerSaveHandler();

            // potential custom logic for session id (ex. switching between hosts)
            $this->setSessionId($this->sidResolver->getSid($this));
            session_start();
            $this->validator->validate($this);

            register_shutdown_function(array($this, 'writeClose'));

            $this->_addHost();
            \Magento\Profiler::stop('session_start');
        }
        $this->storage->init(isset($_SESSION) ? $_SESSION : array());
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
            array($this->saveHandler, 'open'),
            array($this->saveHandler, 'close'),
            array($this->saveHandler, 'read'),
            array($this->saveHandler, 'write'),
            array($this->saveHandler, 'destroy'),
            array($this->saveHandler, 'gc')
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
        return isset($_SESSION[self::HOST_KEY]) ? $_SESSION[self::HOST_KEY] : array();
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
     * @param bool $deleteOldSession
     * @return $this
     */
    public function regenerateId($deleteOldSession = true)
    {
        if (headers_sent()) {
            return $this;
        }
        session_regenerate_id($deleteOldSession);

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
                setcookie(
                    $this->getName(),
                    '',
                    0,
                    $this->sessionConfig->getCookiePath(),
                    $host,
                    $this->sessionConfig->getCookieSecure(),
                    $this->sessionConfig->getCookieHttpOnly()
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
        if (!$this->sessionConfig->getUseCookies()) {
            return;
        }

        setcookie(
            $this->getName(),
            '',
            0,
            $this->sessionConfig->getCookiePath(),
            $this->sessionConfig->getCookieDomain(),
            $this->sessionConfig->getCookieSecure(),
            $this->sessionConfig->getCookieHttpOnly()
        );
        $this->clearSubDomainSessionCookie();
    }
}
