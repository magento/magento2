<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\System\Message;

use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Notification\MessageInterface;
use Magento\Store\Model\Store;
use Zend_Http_Client;
use Zend_Http_Response;

/**
 * Class Security
 *
 * @package Magento\AdminNotification\Model\System\Message
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Security implements MessageInterface
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
    private $_filePath = 'app/etc/config.php'; //phpcs:ignore

    /**
     * Time out for HTTP verification request
     *
     * @var int
     */
    private $_verificationTimeOut = 2; //phpcs:ignore

    /**
     * @var CacheInterface
     */
    protected $_cache; //phpcs:ignore

    /**
     * @var ConfigInterface
     */
    protected $_backendConfig; //phpcs:ignore

    /**
     * @var ScopeConfigInterface
     */
    protected $_config; //phpcs:ignore

    /**
     * @var CurlFactory
     */
    protected $_curlFactory; //phpcs:ignore

    /**
     * @param CacheInterface $cache
     * @param ConfigInterface $backendConfig
     * @param ScopeConfigInterface $config
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        CacheInterface $cache,
        ConfigInterface $backendConfig,
        ScopeConfigInterface $config,
        CurlFactory $curlFactory
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
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    private function _canShowNotification(): bool //phpcs:ignore
    {
        if ($this->_cache->load(self::VERIFICATION_RESULT_CACHE_KEY)) {
            return false;
        }

        if ($this->_isFileAccessible()) {
            return true;
        }

        $adminSessionLifetime = (int)$this->_backendConfig->getValue('admin/security/session_lifetime');
        $this->_cache->save('1', self::VERIFICATION_RESULT_CACHE_KEY, [], $adminSessionLifetime);
        return false;
    }

    /**
     * If file is accessible return true or false
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function _isFileAccessible(): bool //phpcs:ignore
    {
        $unsecureBaseURL = $this->_config->getValue(Store::XML_PATH_UNSECURE_BASE_URL, 'default');

        /** @var \Magento\Framework\HTTP\Adapter\Curl $http */
        $http = $this->_curlFactory->create();
        $http->setConfig(['timeout' => $this->_verificationTimeOut]);
        $http->write(Zend_Http_Client::POST, $unsecureBaseURL . $this->_filePath);
        $responseBody = $http->read();
        $responseCode = Zend_Http_Response::extractCode($responseBody);
        $http->close();

        return $responseCode == 200;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity(): string
    {
        return 'security';
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed(): bool
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
        return MessageInterface::SEVERITY_CRITICAL;
    }
}
