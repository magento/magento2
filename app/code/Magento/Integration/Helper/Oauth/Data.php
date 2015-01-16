<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Helper\Oauth;

/**
 * OAuth View Helper for Controllers
 */
class Data
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**#@+
     * Cleanup xpath config settings
     */
    const XML_PATH_CLEANUP_PROBABILITY = 'oauth/cleanup/cleanup_probability';

    const XML_PATH_CLEANUP_EXPIRATION_PERIOD = 'oauth/cleanup/expiration_period';

    /**#@-*/

    /**
     * Cleanup expiration period in minutes
     */
    const CLEANUP_EXPIRATION_PERIOD_DEFAULT = 120;

    /**#@+
     * Consumer xpath settings
     */
    const XML_PATH_CONSUMER_EXPIRATION_PERIOD = 'oauth/consumer/expiration_period';

    const XML_PATH_CONSUMER_POST_MAXREDIRECTS = 'oauth/consumer/post_maxredirects';

    const XML_PATH_CONSUMER_POST_TIMEOUT = 'oauth/consumer/post_timeout';

    /**#@-*/

    /**#@+
     * Consumer default settings
     */
    const CONSUMER_EXPIRATION_PERIOD_DEFAULT = 300;

    const CONSUMER_POST_TIMEOUT_DEFAULT = 5;

    /**#@-*/

    /**
     * Calculate cleanup possibility for data with lifetime property
     *
     * @return bool
     */
    public function isCleanupProbability()
    {
        // Safe get cleanup probability value from system configuration
        $configValue = (int)$this->_scopeConfig->getValue(
            self::XML_PATH_CLEANUP_PROBABILITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $configValue > 0 ? 1 == \Magento\Framework\Math\Random::getRandomNumber(1, $configValue) : false;
    }

    /**
     * Get cleanup expiration period value from system configuration in minutes
     *
     * @return int
     */
    public function getCleanupExpirationPeriod()
    {
        $minutes = (int)$this->_scopeConfig->getValue(
            self::XML_PATH_CLEANUP_EXPIRATION_PERIOD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $minutes > 0 ? $minutes : self::CLEANUP_EXPIRATION_PERIOD_DEFAULT;
    }

    /**
     * Get consumer expiration period value from system configuration in seconds
     *
     * @return int
     */
    public function getConsumerExpirationPeriod()
    {
        $seconds = (int)$this->_scopeConfig->getValue(
            self::XML_PATH_CONSUMER_EXPIRATION_PERIOD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $seconds > 0 ? $seconds : self::CONSUMER_EXPIRATION_PERIOD_DEFAULT;
    }

    /**
     * Get the number of consumer post maximum redirects
     *
     * @return int
     */
    public function getConsumerPostMaxRedirects()
    {
        $redirects = (int)$this->_scopeConfig->getValue(
            self::XML_PATH_CONSUMER_POST_MAXREDIRECTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $redirects > 0 ? $redirects : 0;
    }

    /**
     * Get the number seconds for the consumer post timeout
     *
     * @return int
     */
    public function getConsumerPostTimeout()
    {
        $seconds = (int)$this->_scopeConfig->getValue(
            self::XML_PATH_CONSUMER_POST_TIMEOUT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $seconds > 0 ? $seconds : self::CONSUMER_POST_TIMEOUT_DEFAULT;
    }
}
