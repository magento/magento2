<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Model\System\Message;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\Store;
use Throwable;

/**
 * @api
 * @since 100.0.2
 */
class Security implements MessageInterface
{
    /**
     * Cache key for saving verification result
     */
    public const VERIFICATION_RESULT_CACHE_KEY = 'configuration_files_access_level_verification';

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
     * @var CacheInterface
     */
    protected $_cache;

    /**
     * @var ConfigInterface
     */
    protected $_backendConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var CurlFactory
     */
    protected $_curlFactory;

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

        /** @var $http Curl */
        $http = $this->_curlFactory->create();
        $http->setOptions(['timeout' => $this->_verificationTimeOut]);
        $http->write(Request::METHOD_POST, $unsecureBaseURL . $this->_filePath);
        $responseBody = $http->read();
        $responseCode = $this->extractCodeFromResponse($responseBody);
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
     * @return Phrase
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

    /**
     * Extract the response code from a response string
     *
     * @param string $responseString
     *
     * @return false|int
     */
    private function extractCodeFromResponse(string $responseString)
    {
        try {
            $responseCode = Response::fromString($responseString)->getStatusCode();
        } catch (Throwable $e) {
            $responseCode = false;
        }

        return $responseCode;
    }
}
