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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer address helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Helper;

class Address extends \Magento\App\Helper\AbstractHelper
{
    /**
     * VAT Validation parameters XML paths
     */
    const XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT = 'customer/create_account/viv_disable_auto_group_assign_default';
    const XML_PATH_VIV_ON_EACH_TRANSACTION         = 'customer/create_account/viv_on_each_transaction';
    const XML_PATH_VAT_VALIDATION_ENABLED          = 'customer/create_account/auto_group_assign';
    const XML_PATH_VIV_TAX_CALCULATION_ADDRESS_TYPE = 'customer/create_account/tax_calculation_address_type';
    const XML_PATH_VAT_FRONTEND_VISIBILITY = 'customer/create_account/vat_frontend_visibility';

    /**
     * Array of Customer Address Attributes
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Customer address config node per website
     *
     * @var array
     */
    protected $_config          = array();

    /**
     * Customer Number of Lines in a Street Address per website
     *
     * @var array
     */
    protected $_streetLines     = array();
    protected $_formatTemplate  = array();

    /**
     * Block factory
     *
     * @var \Magento\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\View\Element\BlockFactory $blockFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Address\Config|\Magento\Customer\Model\Address\Config $addressConfig
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\View\Element\BlockFactory $blockFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_blockFactory = $blockFactory;
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context);
    }

    /**
     * Addresses url
     */
    public function getBookUrl()
    {

    }

    public function getEditUrl()
    {

    }

    public function getDeleteUrl()
    {

    }

    public function getCreateUrl()
    {

    }

    public function getRenderer($renderer)
    {
        if (is_string($renderer) && $renderer) {
            return $this->_blockFactory->createBlock($renderer, array());
        } else {
            return $renderer;
        }
    }

    /**
     * Return customer address config value by key and store
     *
     * @param string $key
     * @param \Magento\Core\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();
        if (!isset($this->_config[$websiteId])) {
            $this->_config[$websiteId] = $store->getConfig('customer/address', $store);
        }
        return isset($this->_config[$websiteId][$key]) ? (string)$this->_config[$websiteId][$key] : null;
    }

    /**
     * Return Number of Lines in a Street Address for store
     *
     * @param \Magento\Core\Model\Store|int|string $store
     * @return int
     */
    public function getStreetLines($store = null)
    {
        $websiteId = $this->_storeManager->getStore($store)->getWebsiteId();
        if (!isset($this->_streetLines[$websiteId])) {
            $attribute = $this->_eavConfig->getAttribute('customer_address', 'street');
            $lines = (int)$attribute->getMultilineCount();
            if ($lines <= 0) {
                $lines = 2;
            }
            $this->_streetLines[$websiteId] = min(4, $lines);
        }

        return $this->_streetLines[$websiteId];
    }

    public function getFormat($code)
    {
        $format = $this->_addressConfig->getFormatByCode($code);
        return $format->getRenderer() ? $format->getRenderer()->getFormatArray() : '';
    }

    /**
     * Retrieve renderer by code
     *
     * @param string $code
     * @return \Magento\Customer\Block\Address\Renderer\RendererInterface
     */
    public function getFormatTypeRenderer($code)
    {
        $formatType = $this->_addressConfig->getFormatByCode($code);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }
        return $formatType->getRenderer();
    }

    /**
     * Determine if specified address config value can be shown
     *
     * @param string $key
     * @return bool
     */
    public function canShowConfig($key)
    {
        return (bool)$this->getConfig($key);
    }

    /**
     * Return array of Customer Address Attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if (is_null($this->_attributes)) {
            $this->_attributes = array();
            $config = $this->_eavConfig;
            foreach ($config->getEntityAttributeCodes('customer_address') as $attributeCode) {
                $this->_attributes[$attributeCode] = $config->getAttribute('customer_address', $attributeCode);
            }
        }
        return $this->_attributes;
    }

    /**
     * Get string with frontend validation classes for attribute
     *
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeValidationClass($attributeCode)
    {
        /** @var $attribute \Magento\Customer\Model\Attribute */
        $attribute = isset($this->_attributes[$attributeCode]) ? $this->_attributes[$attributeCode]
            : $this->_eavConfig->getAttribute('customer_address', $attributeCode);
        $class = $attribute ? $attribute->getFrontend()->getClass() : '';

        if (in_array($attributeCode, array('firstname', 'middlename', 'lastname', 'prefix', 'suffix', 'taxvat'))) {
            if ($class && !$attribute->getIsVisible()) {
                $class = ''; // address attribute is not visible thus its validation rules are not applied
            }

            /** @var $customerAttribute \Magento\Customer\Model\Attribute */
            $customerAttribute = $this->_eavConfig->getAttribute('customer', $attributeCode);
            $class .= $customerAttribute && $customerAttribute->getIsVisible()
                ? $customerAttribute->getFrontend()->getClass() : '';
            $class = implode(' ', array_unique(array_filter(explode(' ', $class))));
        }

        return $class;
    }

    /**
     * Convert streets array to new street lines count
     * Examples of use:
     *  $origStreets = array('street1', 'street2', 'street3', 'street4')
     *  $toCount = 3
     *  Result:
     *   array('street1 street2', 'street3', 'street4')
     *  $toCount = 2
     *  Result:
     *   array('street1 street2', 'street3 street4')
     *
     * @param array $origStreets
     * @param int   $toCount
     * @return array
     */
    public function convertStreetLines($origStreets, $toCount)
    {
        $lines = array();
        if (!empty($origStreets) && $toCount > 0) {
            $countArgs = (int)floor(count($origStreets)/$toCount);
            $modulo = count($origStreets) % $toCount;
            $offset = 0;
            $neededLinesCount = 0;
            for ($i = 0; $i < $toCount; $i++) {
                $offset += $neededLinesCount;
                $neededLinesCount = $countArgs;
                if ($modulo > 0) {
                    ++$neededLinesCount;
                    --$modulo;
                }
                $values = array_slice($origStreets, $offset, $neededLinesCount);
                if (is_array($values)) {
                    $lines[] = implode(' ', $values);
                }
            }
        }

        return $lines;
    }

    /**
     * Check whether VAT ID validation is enabled
     *
     * @param \Magento\Core\Model\Store|string|int $store
     * @return bool
     */
    public function isVatValidationEnabled($store = null)
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_VAT_VALIDATION_ENABLED, $store);
    }

    /**
     * Retrieve disable auto group assign default value
     *
     * @return bool
     */
    public function getDisableAutoGroupAssignDefaultValue()
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT);
    }

    /**
     * Retrieve 'validate on each transaction' value
     *
     * @param \Magento\Core\Model\Store|string|int $store
     * @return bool
     */
    public function getValidateOnEachTransaction($store = null)
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_VIV_ON_EACH_TRANSACTION, $store);
    }

    /**
     * Retrieve customer address type on which tax calculation must be based
     *
     * @param \Magento\Core\Model\Store|string|int|null $store
     * @return string
     */
    public function getTaxCalculationAddressType($store = null)
    {
        return (string)$this->_coreStoreConfig->getConfig(self::XML_PATH_VIV_TAX_CALCULATION_ADDRESS_TYPE, $store);
    }

    /**
     * Check if VAT ID address attribute has to be shown on frontend (on Customer Address management forms)
     *
     * @return boolean
     */
    public function isVatAttributeVisible()
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_VAT_FRONTEND_VISIBILITY);
    }
}
