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
namespace Magento\Customer\Helper;

use Magento\Directory\Model\Country\Format;
use Magento\Customer\Service\V1\Data\Eav\AttributeMetadata;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer address helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Address extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * VAT Validation parameters XML paths
     */
    const XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT = 'customer/create_account/viv_disable_auto_group_assign_default';

    const XML_PATH_VIV_ON_EACH_TRANSACTION = 'customer/create_account/viv_on_each_transaction';

    const XML_PATH_VAT_VALIDATION_ENABLED = 'customer/create_account/auto_group_assign';

    const XML_PATH_VIV_TAX_CALCULATION_ADDRESS_TYPE = 'customer/create_account/tax_calculation_address_type';

    const XML_PATH_VAT_FRONTEND_VISIBILITY = 'customer/create_account/vat_frontend_visibility';

    /**
     * Possible customer address types
     */
    const TYPE_BILLING = 'billing';

    const TYPE_SHIPPING = 'shipping';

    /**
     * Array of Customer Address Attributes
     *
     * @var AttributeMetadata[]
     */
    protected $_attributes;

    /**
     * Customer address config node per website
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Customer Number of Lines in a Street Address per website
     *
     * @var array
     */
    protected $_streetLines = array();

    /**
     * @var array
     */
    protected $_formatTemplate = array();

    /** @var \Magento\Framework\View\Element\BlockFactory */
    protected $_blockFactory;

    /** @var \Magento\Framework\StoreManagerInterface */
    protected $_storeManager;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /** @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface */
    protected $_customerMetadataService;

    /** @var \Magento\Customer\Service\V1\AddressMetadataServiceInterface */
    protected $_addressMetadataService;

    /** @var \Magento\Customer\Model\Address\Config*/
    protected $_addressConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $customerMetadataService
     * @param \Magento\Customer\Service\V1\AddressMetadataServiceInterface $addressMetadataService
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $customerMetadataService,
        \Magento\Customer\Service\V1\AddressMetadataServiceInterface $addressMetadataService,
        \Magento\Customer\Model\Address\Config $addressConfig
    ) {
        $this->_blockFactory = $blockFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_customerMetadataService = $customerMetadataService;
        $this->_addressMetadataService = $addressMetadataService;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context);
    }

    /**
     * Addresses url
     *
     * @return void
     */
    public function getBookUrl()
    {
    }

    /**
     * @return void
     */
    public function getEditUrl()
    {
    }

    /**
     * @return void
     */
    public function getDeleteUrl()
    {
    }

    /**
     * @return void
     */
    public function getCreateUrl()
    {
    }

    /**
     * @param string $renderer
     * @return \Magento\Framework\View\Element\BlockInterface
     */
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
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();
        if (!isset($this->_config[$websiteId])) {
            $this->_config[$websiteId] = $this->_scopeConfig->getValue(
                'customer/address',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        return isset($this->_config[$websiteId][$key]) ? (string)$this->_config[$websiteId][$key] : null;
    }

    /**
     * Return Number of Lines in a Street Address for store
     *
     * @param \Magento\Store\Model\Store|int|string $store
     * @return int
     */
    public function getStreetLines($store = null)
    {
        $websiteId = $this->_storeManager->getStore($store)->getWebsiteId();
        if (!isset($this->_streetLines[$websiteId])) {
            $attribute = $this->_addressMetadataService->getAttributeMetadata('street');

            $lines = $attribute->getMultilineCount();
            if ($lines <= 0) {
                $lines = 2;
            }
            $this->_streetLines[$websiteId] = min($lines, 20);
        }

        return $this->_streetLines[$websiteId];
    }

    /**
     * @param string $code
     * @return Format|string
     */
    public function getFormat($code)
    {
        $format = $this->_addressConfig->getFormatByCode($code);
        return $format->getRenderer() ? $format->getRenderer()->getFormatArray() : '';
    }

    /**
     * Retrieve renderer by code
     *
     * @param string $code
     * @return \Magento\Customer\Block\Address\Renderer\RendererInterface|null
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
     * Get string with frontend validation classes for attribute
     *
     * @param string $attributeCode
     * @return string
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getAttributeValidationClass($attributeCode)
    {
        $class = '';

        try {
            /** @var $attribute \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata */
            $attribute = isset($this->_attributes[$attributeCode])
                ? $this->_attributes[$attributeCode]
                : $this->_addressMetadataService->getAttributeMetadata($attributeCode);
            $class = $attribute ? $attribute->getFrontendClass() : '';
            if (in_array($attributeCode, array('firstname', 'middlename', 'lastname', 'prefix', 'suffix', 'taxvat'))) {
                if ($class && !$attribute->isVisible()) {
                    // address attribute is not visible thus its validation rules are not applied
                    $class = '';
                }

                /** @var $customerAttribute \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata */
                $customerAttribute = $this->_customerMetadataService->getAttributeMetadata($attributeCode);
                $class .= $customerAttribute &&
                    $customerAttribute->isVisible() ? $customerAttribute->getFrontendClass() : '';
                $class = implode(' ', array_unique(array_filter(explode(' ', $class))));
            }
        } catch (NoSuchEntityException $e) {
            // the attribute does not exist so just return an empty string
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
     * @param string[] $origStreets
     * @param int $toCount
     * @return string[]
     */
    public function convertStreetLines($origStreets, $toCount)
    {
        $lines = array();
        if (!empty($origStreets) && $toCount > 0) {
            $countArgs = (int)floor(count($origStreets) / $toCount);
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
     * @param \Magento\Store\Model\Store|string|int $store
     * @return bool
     */
    public function isVatValidationEnabled($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_VAT_VALIDATION_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve disable auto group assign default value
     *
     * @return bool
     */
    public function isDisableAutoGroupAssignDefaultValue()
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve 'validate on each transaction' value
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return bool
     */
    public function hasValidateOnEachTransaction($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_VIV_ON_EACH_TRANSACTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve customer address type on which tax calculation must be based
     *
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return string
     */
    public function getTaxCalculationAddressType($store = null)
    {
        return (string)$this->_scopeConfig->getValue(
            self::XML_PATH_VIV_TAX_CALCULATION_ADDRESS_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if VAT ID address attribute has to be shown on frontend (on Customer Address management forms)
     *
     * @return boolean
     */
    public function isVatAttributeVisible()
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_VAT_FRONTEND_VISIBILITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
