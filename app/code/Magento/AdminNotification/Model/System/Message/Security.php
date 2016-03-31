<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Model\System\Message;

use Magento\Store\Model\Store;

class Security implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * Cache key for saving verification result
     */
    const VERIFICATION_RESULT_CACHE_KEY = 'configuration_files_access_level_verification';

    /**
     * File path for verification
     *
     * @var string
     */
    private $_filePath = 'app/etc/config.php';

    /**
     * Time out for HTTP verification request
     *
     * @var int
     */
    private $_verificationTimeOut = 2;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    protected $_curlFactory;

    /**
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
    ) {
        $this->_cache = $cache;
        $this->_backendConfig = $backendConfig;
        $this->_config = $config;
        $this->_curlFactory = $curlFactory;
    }

    /**
     * Check verification result and return true if system must to show notification message
     *
     * @return bool
     */
    private function _canShowNotification()
    {
        if ($this->_cache->load(self::VERIFICATION_RESULT_CACHE_KEY)) {
            return false;
        }

        if ($this->_isFileAccessible()) {
            return true;
        }

        $adminSessionLifetime = (int)$this->_backendConfig->getValue('admin/security/session_lifetime');
        $this->_cache->save(true, self::VERIFICATION_RESULT_CACHE_KEY, [], $adminSessionLifetime);
        return false;
    }

    /**
     * If file is accessible return true or false
     *
     * @return bool
     */
    private function _isFileAccessible()
    {
        $unsecureBaseURL = $this->_config->getValue(Store::XML_PATH_UNSECURE_BASE_URL, 'default');

        /** @var $http \Magento\Framework\HTTP\Adapter\Curl */
        $http = $this->_curlFactory->create();
        $http->setConfig(['timeout' => $this->_verificationTimeOut]);
        $http->write(\Zend_Http_Client::POST, $unsecureBaseURL . $this->_filePath);
        $responseBody = $http->read();
        $responseCode = \Zend_Http_Response::extractCode($responseBody);
        $http->close();

        return $responseCode == 200;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return 'security';
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->_canShowNotification();
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __(
            'Your web server is set up incorrectly and allows unauthorized access to sensitive files. '
            . 'Please contact your hosting provider.'
        );
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
    }
}
