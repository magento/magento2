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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Config centinel model
 */
namespace Magento\Centinel\Model;

class Config
{
    /**
     * Store id or store model
     *
     * @var int|\Magento\Store\Model\Store
     */
    protected $_store = false;

    /**
     * Path of centinel config
     *
     * @var string
     */
    protected $_serviceConfigPath = 'payment_services/centinel';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Core config interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * Encryptor interface
     *
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_coreConfig = $coreConfig;
        $this->_encryptor = $encryptor;
    }

    /**
     * Set store to congif model
     *
     * @param int|\Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Return store
     *
     * @return int|\Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     * Return centinel processorId
     *
     * @return string
     */
    public function getProcessorId()
    {
        return $this->_getServiceConfigValue('processor_id');
    }

    /**
     * Return centinel merchantId
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->_getServiceConfigValue('merchant_id');
    }

    /**
     * Return centinel transactionPwd
     *
     * @return string
     */
    public function getTransactionPwd()
    {
        return $this->_encryptor->decrypt($this->_getServiceConfigValue('password'));
    }

    /**
     * Return flag - is centinel mode test
     *
     * @return bool
     */
    public function getIsTestMode()
    {
        return (bool)(int)$this->_getServiceConfigValue('test_mode');
    }

    /**
     * Return value of node of centinel config section
     *
     * @param string $key
     * @return string
     */
    private function _getServiceConfigValue($key)
    {
        return $this->_scopeConfig->getValue($this->_serviceConfigPath . '/' . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore());
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag()
    {
        return $this->_getServiceConfigValue('debug');
    }
}
