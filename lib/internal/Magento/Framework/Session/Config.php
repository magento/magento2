<?php
/**
 * Session configuration object
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\SaveHandlerInterface;

/**
 * Magento session configuration
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Config implements ConfigInterface
{
    /** Configuration path for session save method */
    const PARAM_SESSION_SAVE_METHOD = 'session/save';

    /** Configuration path for session save path */
    const PARAM_SESSION_SAVE_PATH = 'session/save_path';

    /** Configuration path for session cache limiter */
    const PARAM_SESSION_CACHE_LIMITER = 'session/cache_limiter';

    /** Configuration path for cookie domain */
    const XML_PATH_COOKIE_DOMAIN = 'web/cookie/cookie_domain';

    /** Configuration path for cookie lifetime */
    const XML_PATH_COOKIE_LIFETIME = 'web/cookie/cookie_lifetime';

    /** Configuration path for cookie http only param */
    const XML_PATH_COOKIE_HTTPONLY = 'web/cookie/cookie_httponly';

    /** Configuration path for cookie path */
    const XML_PATH_COOKIE_PATH = 'web/cookie/cookie_path';

    /** Cookie default lifetime */
    const COOKIE_LIFETIME_DEFAULT = 3600;

    /**
     * All options
     *
     * @var array
     */
    protected $options = [];

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /** @var \Magento\Framework\Stdlib\StringUtils */
    protected $_stringHelper;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $_httpRequest;

    /**
     * List of boolean options
     *
     * @var string[]
     */
    protected $booleanOptions = [
        'session.use_cookies',
        'session.use_only_cookies',
        'session.use_trans_sid',
        'session.cookie_httponly',
    ];

    /** @var string */
    protected $_scopeType;

    /** @var string */
    protected $lifetimePath;

    /** @var \Magento\Framework\ValidatorFactory */
    protected $_validatorFactory;

    /**
     * @param \Magento\Framework\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Filesystem $filesystem
     * @param DeploymentConfig $deploymentConfig
     * @param string $scopeType
     * @param string $lifetimePath
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(
        \Magento\Framework\ValidatorFactory $validatorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\StringUtils $stringHelper,
        \Magento\Framework\App\RequestInterface $request,
        Filesystem $filesystem,
        DeploymentConfig $deploymentConfig,
        $scopeType,
        $lifetimePath = self::XML_PATH_COOKIE_LIFETIME
    ) {
        $this->_validatorFactory = $validatorFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_stringHelper = $stringHelper;
        $this->_httpRequest = $request;
        $this->_scopeType = $scopeType;
        $this->lifetimePath = $lifetimePath;

        /**
         * Session path
         */
        $savePath = $deploymentConfig->get(self::PARAM_SESSION_SAVE_PATH);
        if (!$savePath && !ini_get('session.save_path')) {
            $sessionDir = $filesystem->getDirectoryWrite(DirectoryList::SESSION);
            $savePath = $sessionDir->getAbsolutePath();
            $sessionDir->create();
        }
        if ($savePath) {
            $this->setSavePath($savePath);
        }

        /**
         * Session cache limiter
         */
        $cacheLimiter = $deploymentConfig->get(self::PARAM_SESSION_CACHE_LIMITER);
        if ($cacheLimiter) {
            $this->setOption('session.cache_limiter', $cacheLimiter);
        }

        /**
         * Cookie settings: lifetime, path, domain, httpOnly. These govern settings for the session cookie.
         */
        $this->configureCookieLifetime();

        $path = $this->_scopeConfig->getValue(self::XML_PATH_COOKIE_PATH, $this->_scopeType);
        $path = empty($path) ? $this->_httpRequest->getBasePath() : $path;
        $this->setCookiePath($path, $this->_httpRequest->getBasePath());

        $domain = $this->_scopeConfig->getValue(self::XML_PATH_COOKIE_DOMAIN, $this->_scopeType);
        $domain = empty($domain) ? $this->_httpRequest->getHttpHost() : $domain;
        $this->setCookieDomain((string)$domain, $this->_httpRequest->getHttpHost());

        $this->setCookieHttpOnly(
            $this->_scopeConfig->getValue(self::XML_PATH_COOKIE_HTTPONLY, $this->_scopeType)
        );

        $secureURL = $this->_scopeConfig->getValue('web/secure/base_url', $this->_scopeType);
        $unsecureURL = $this->_scopeConfig->getValue('web/unsecure/base_url', $this->_scopeType);
        $isFullySecuredURL = $secureURL == $unsecureURL;
        $this->setCookieSecure($isFullySecuredURL && $this->_httpRequest->isSecure());
    }

    /**
     * Set many options at once
     *
     * @param array $options
     * @param array $default
     * @return $this
     */
    public function setOptions($options, $default = [])
    {
        $options = (!is_array($options) && !$options instanceof \Traversable) ? $default : $options;
        if (is_array($options) || $options instanceof \Traversable) {
            foreach ($options as $option => $value) {
                $setter = 'set' . $this->_stringHelper->upperCaseWords($option, '_', '');
                if (method_exists($this, $setter)) {
                    $this->{$setter}($value);
                } else {
                    $this->setOption($option, $value);
                }
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
     * @param string|null $default
     * @return $this
     */
    public function setName($name, $default = null)
    {
        $name = (string)$name;
        $name = empty($name) ? $default : $name;
        if (!empty($name)) {
            $this->setOption('session.name', $name);
        }

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
     * @param int|null $default
     * @return $this
     */
    public function setCookieLifetime($cookieLifetime, $default = null)
    {
        $validator = $this->_validatorFactory->create(
            [],
            \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator::class
        );
        if ($validator->isValid($cookieLifetime)) {
            $this->setOption('session.cookie_lifetime', (int)$cookieLifetime);
        } elseif (null !== $default && $validator->isValid($default)) {
            $this->setOption('session.cookie_lifetime', (int)$default);
        }

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
     * @param string|null $default
     * @return $this
     */
    public function setCookiePath($cookiePath, $default = null)
    {
        $cookiePath = (string)$cookiePath;
        $validator = $this->_validatorFactory->create(
            [],
            \Magento\Framework\Session\Config\Validator\CookiePathValidator::class
        );
        if ($validator->isValid($cookiePath)) {
            $this->setOption('session.cookie_path', $cookiePath);
        } elseif (null !== $default && $validator->isValid($default)) {
            $this->setOption('session.cookie_path', $default);
        }

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
     * @param string|null $default
     * @return $this
     */
    public function setCookieDomain($cookieDomain, $default = null)
    {
        $validator = $this->_validatorFactory->create(
            [],
            \Magento\Framework\Session\Config\Validator\CookieDomainValidator::class
        );
        if ($validator->isValid($cookieDomain)) {
            $this->setOption('session.cookie_domain', $cookieDomain);
        } elseif (null !== $default && $validator->isValid($default)) {
            $this->setOption('session.cookie_domain', $default);
        }

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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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

    /**
     * Set session cookie lifetime according to configuration
     *
     * @return $this
     */
    protected function configureCookieLifetime()
    {
        $lifetime = $this->_scopeConfig->getValue($this->lifetimePath, $this->_scopeType);
        return $this->setCookieLifetime($lifetime, self::COOKIE_LIFETIME_DEFAULT);
    }
}
