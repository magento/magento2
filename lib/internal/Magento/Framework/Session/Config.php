<?php
/**
 * Session configuration object
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
namespace Magento\Framework\Session;

use Magento\Framework\Session\Config\ConfigInterface;

/**
 * Magento session configuration
 *
 * @method Config setSaveHandler()
 */
class Config implements ConfigInterface
{
    /**
     * Configuration path for session save method
     */
    const PARAM_SESSION_SAVE_METHOD = 'session_save';

    /**
     * Configuration path for session save path
     */
    const PARAM_SESSION_SAVE_PATH = 'session_save_path';

    /**
     * Configuration path for session cache limiter
     */
    const PARAM_SESSION_CACHE_LIMITER = 'session_cache_limiter';

    /**
     * Configuration path for cookie domain
     */
    const XML_PATH_COOKIE_DOMAIN = 'web/cookie/cookie_domain';

    /**
     * Configuration path for cookie lifetime
     */
    const XML_PATH_COOKIE_LIFETIME = 'web/cookie/cookie_lifetime';

    /**
     * Configuration path for cookie http only param
     */
    const XML_PATH_COOKIE_HTTPONLY = 'web/cookie/cookie_httponly';

    /**
     * Configuration path for cookie path
     */
    const XML_PATH_COOKIE_PATH = 'web/cookie/cookie_path';

    /**
     * Cookie default lifetime
     */
    const COOKIE_LIFETIME_DEFAULT = 3600;

    /**
     * All options
     *
     * @var array
     */
    protected $options = array();

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $_stringHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_httpRequest;

    /**
     * List of boolean options
     *
     * @var string[]
     */
    protected $booleanOptions = array(
        'session.use_cookies',
        'session.use_only_cookies',
        'session.use_trans_sid',
        'session.cookie_httponly'
    );

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @var string
     */
    protected $_scopeType;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\String $stringHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param string $scopeType
     * @param string $saveMethod
     * @param null|string $savePath
     * @param null|string $cacheLimiter
     * @param string $lifetimePath
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\String $stringHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Filesystem $filesystem,
        $scopeType,
        $saveMethod = \Magento\Framework\Session\SaveHandlerInterface::DEFAULT_HANDLER,
        $savePath = null,
        $cacheLimiter = null,
        $lifetimePath = self::XML_PATH_COOKIE_LIFETIME
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_stringHelper = $stringHelper;
        $this->_httpRequest = $request;
        $this->_appState = $appState;
        $this->_filesystem = $filesystem;
        $this->_scopeType = $scopeType;

        $this->setSaveHandler($saveMethod === 'db' ? 'user' : $saveMethod);

        if (!$this->_appState->isInstalled() || !$savePath) {
            $savePath = $this->_filesystem->getPath('session');
        }
        $this->setSavePath($savePath);

        if ($cacheLimiter) {
            $this->setOption('session.cache_limiter', $cacheLimiter);
        }

        $lifetime = $this->_scopeConfig->getValue($lifetimePath, $this->_scopeType);
        $lifetime = is_numeric($lifetime) ? $lifetime : self::COOKIE_LIFETIME_DEFAULT;
        $this->setCookieLifetime($lifetime);

        $path = $this->_scopeConfig->getValue(self::XML_PATH_COOKIE_PATH, $this->_scopeType);
        if (empty($path)) {
            $path = $this->_httpRequest->getBasePath();
        }
        $this->setCookiePath($path);

        $domain = $this->_scopeConfig->getValue(self::XML_PATH_COOKIE_DOMAIN, $this->_scopeType);
        $domain = empty($domain) ? $this->_httpRequest->getHttpHost() : $domain;
        $this->setCookieDomain((string)$domain);

        $this->setCookieHttpOnly(
            $this->_scopeConfig->getValue(self::XML_PATH_COOKIE_HTTPONLY, $this->_scopeType)
        );
    }

    /**
     * Set many options at once
     *
     * @param array $options
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof \Traversable) {
            throw new \InvalidArgumentException(
                sprintf('Parameter provided to %s must be an array or Traversable', __METHOD__)
            );
        }

        foreach ($options as $option => $value) {
            $setter = 'set' . $this->_stringHelper->upperCaseWords($option, '_', '');
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            } else {
                $this->setOption($option, $value);
            }
        }

        return $this;
    }

    /**
     * Get all options set
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set an individual option
     *
     * @param string $option
     * @param mixed $value
     * @return $this
     */
    public function setOption($option, $value)
    {
        $option = $this->getFixedOptionName($option);
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Get an individual option
     *
     * @param string $option
     * @return mixed
     */
    public function getOption($option)
    {
        $option = $this->getFixedOptionName($option);
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        $value = $this->getStorageOption($option);
        if (null !== $value) {
            $this->options[$option] = $value;
            return $value;
        }

        return null;
    }

    /**
     * Convert config to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getOptions();
    }

    /**
     * Set session.name
     *
     * @param string $name
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        $name = (string)$name;
        if (empty($name)) {
            throw new \InvalidArgumentException('Invalid session name; cannot be empty');
        }
        $this->setOption('session.name', $name);
        return $this;
    }

    /**
     * Get session.name
     *
     * @return string
     */
    public function getName()
    {
        return (string)$this->getOption('session.name');
    }

    /**
     * Set session.save_path
     *
     * @param string $savePath
     * @return $this
     */
    public function setSavePath($savePath)
    {
        $this->setOption('session.save_path', $savePath);
        return $this;
    }

    /**
     * Set session.save_path
     *
     * @return string
     */
    public function getSavePath()
    {
        return (string)$this->getOption('session.save_path');
    }

    /**
     * Set session.cookie_lifetime
     *
     * @param int $cookieLifetime
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setCookieLifetime($cookieLifetime)
    {
        if (!is_numeric($cookieLifetime)) {
            throw new \InvalidArgumentException('Invalid cookie_lifetime; must be numeric');
        }
        if ($cookieLifetime < 0) {
            throw new \InvalidArgumentException('Invalid cookie_lifetime; must be a positive integer or zero');
        }

        $cookieLifetime = (int)$cookieLifetime;
        $this->setOption('session.cookie_lifetime', $cookieLifetime);
        return $this;
    }

    /**
     * Get session.cookie_lifetime
     *
     * @return int
     */
    public function getCookieLifetime()
    {
        return (int)$this->getOption('session.cookie_lifetime');
    }

    /**
     * Set session.cookie_path
     *
     * @param string $cookiePath
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setCookiePath($cookiePath)
    {
        $cookiePath = (string)$cookiePath;

        $test = parse_url($cookiePath, PHP_URL_PATH);
        if ($test != $cookiePath || '/' != $test[0]) {
            throw new \InvalidArgumentException('Invalid cookie path');
        }

        $this->setOption('session.cookie_path', $cookiePath);
        return $this;
    }

    /**
     * Get session.cookie_path
     *
     * @return string
     */
    public function getCookiePath()
    {
        return (string)$this->getOption('session.cookie_path');
    }

    /**
     * Set session.cookie_domain
     *
     * @param string $cookieDomain
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setCookieDomain($cookieDomain)
    {
        if (!is_string($cookieDomain)) {
            throw new \InvalidArgumentException('Invalid cookie domain: must be a string');
        }

        $validator = new \Zend\Validator\Hostname(\Zend\Validator\Hostname::ALLOW_ALL);

        if (!empty($cookieDomain) && !$validator->isValid($cookieDomain)) {
            throw new \InvalidArgumentException('Invalid cookie domain: ' . join('; ', $validator->getMessages()));
        }

        $this->setOption('session.cookie_domain', $cookieDomain);
        return $this;
    }

    /**
     * Get session.cookie_domain
     *
     * @return string
     */
    public function getCookieDomain()
    {
        return (string)$this->getOption('session.cookie_domain');
    }

    /**
     * Set session.cookie_secure
     *
     * @param bool $cookieSecure
     * @return $this
     */
    public function setCookieSecure($cookieSecure)
    {
        $this->setOption('session.cookie_secure', (bool)$cookieSecure);
        return $this;
    }

    /**
     * Get session.cookie_secure
     *
     * @return bool
     */
    public function getCookieSecure()
    {
        return (bool)$this->getOption('session.cookie_secure');
    }

    /**
     * Set session.cookie_httponly
     *
     * @param bool $cookieHttpOnly
     * @return $this
     */
    public function setCookieHttpOnly($cookieHttpOnly)
    {
        $this->setOption('session.cookie_httponly', (bool)$cookieHttpOnly);
        return $this;
    }

    /**
     * Get session.cookie_httponly
     *
     * @return bool
     */
    public function getCookieHttpOnly()
    {
        return (bool)$this->getOption('session.cookie_httponly');
    }

    /**
     * Set session.use_cookies
     *
     * @param bool $useCookies
     * @return $this
     */
    public function setUseCookies($useCookies)
    {
        $this->setOption('session.use_cookies', (bool)$useCookies);
        return $this;
    }

    /**
     * Get session.use_cookies
     *
     * @return bool
     */
    public function getUseCookies()
    {
        return (bool)$this->getOption('session.use_cookies');
    }

    /**
     * Retrieve a storage option from a backend configuration store
     *
     * @param string $option
     * @return string|bool
     */
    protected function getStorageOption($option)
    {
        $value = ini_get($option);
        if (in_array($option, $this->booleanOptions)) {
            $value = (bool)$value;
        }

        return $value;
    }

    /**
     * Fix session option name
     *
     * @param string $option
     * @return string
     */
    protected function getFixedOptionName($option)
    {
        $option = strtolower($option);

        switch ($option) {
            case 'url_rewriter_tags':
                $option = 'url_rewriter.tags';
                break;
            default:
                if (strpos($option, 'session.') !== 0) {
                    $option = 'session.' . $option;
                }
                break;
        }

        return $option;
    }

    /**
     * Intercept get*() and set*() methods
     *
     * Intercepts getters and setters and passes them to getOption() and setOption(),
     * respectively.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws \BadMethodCallException On non-getter/setter method
     */
    public function __call($method, $args)
    {
        $prefix = substr($method, 0, 3);
        $option = substr($method, 3);
        $key = strtolower(preg_replace('#(?<=[a-z])([A-Z])#', '_\1', $option));

        if ($prefix === 'set') {
            $value = array_shift($args);
            return $this->setOption($key, $value);
        } elseif ($prefix === 'get') {
            return $this->getOption($key);
        } else {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist in %s', $method, get_class($this)));
        }
    }
}
