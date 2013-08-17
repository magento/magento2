<?php
/**
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_AdminNotification_Model_System_Message_Security
    implements Mage_AdminNotification_Model_System_MessageInterface
{
    /**
     * Cache kay for saving verification result
     */
    const VERIFICATION_RESULT_CACHE_KEY = 'configuration_files_access_level_verification';

    /**
     * File path for verification
     *
     * @var string
     */
    private $_filePath = 'app/etc/local.xml';

    /**
     * Time out for HTTP verification request
     * @var int
     */
    private $_verificationTimeOut  = 2;

    /**
     * @var Mage_Core_Model_CacheInterface
     */
    protected $_cache;

    /**
     * @var Mage_Core_Model_Store_Config
     */
    protected $_storeConfig;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @var Varien_Http_Adapter_CurlFactory
     */
    protected $_curlFactory;

    /**
     * @var Mage_Core_Model_Factory_Helper
     */
    protected $_helperFactory;

    /**
     * @param Mage_Core_Model_CacheInterface $cache
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Model_Config $config
     * @param Varien_Http_Adapter_CurlFactory $curlFactory
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     */
    public function __construct(
        Mage_Core_Model_CacheInterface $cache,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Model_Config $config,
        Varien_Http_Adapter_CurlFactory $curlFactory,
        Mage_Core_Model_Factory_Helper $helperFactory
    ) {
        $this->_cache = $cache;
        $this->_storeConfig = $storeConfig;
        $this->_config = $config;
        $this->_curlFactory = $curlFactory;
        $this->_helperFactory = $helperFactory;
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

        $adminSessionLifetime = (int) $this->_storeConfig->getConfig('admin/security/session_lifetime');
        $this->_cache->save(true, self::VERIFICATION_RESULT_CACHE_KEY, array(), $adminSessionLifetime);
        return false;
    }

    /**
     * If file is accessible return true or false
     *
     * @return bool
     */
    private function _isFileAccessible()
    {
        $unsecureBaseURL = (string) $this->_config->getNode(
            'default/' . Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL
        );

        /** @var $http Varien_Http_Adapter_Curl */
        $http = $this->_curlFactory->create();
        $http->setConfig(array('timeout' => $this->_verificationTimeOut));
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
     * @return string
     */
    public function getText()
    {
        return $this->_helperFactory->get('Mage_AdminNotification_Helper_Data')->__('Your web server is configured incorrectly. As a result, configuration files with sensitive information are accessible from the outside. Please contact your hosting provider.');
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return Mage_AdminNotification_Model_System_MessageInterface::SEVERITY_CRITICAL;
    }
}
