<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Directory\Model\Country\Format;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer address helper
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
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
     * @var AttributeMetadataInterface[]
     * @since 2.0.0
     */
    protected $_attributes;

    /**
     * Customer address config node per website
     *
     * @var array
     * @since 2.0.0
     */
    protected $_config = [];

    /**
     * Customer Number of Lines in a Street Address per website
     *
     * @var array
     * @since 2.0.0
     */
    protected $_streetLines = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_formatTemplate = [];

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     * @since 2.0.0
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var CustomerMetadataInterface
     *
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $_customerMetadataService;

    /**
     * @var \Magento\Customer\Api\AddressMetadataInterface
     * @since 2.0.0
     */
    protected $_addressMetadataService;

    /**
     * @var \Magento\Customer\Model\Address\Config
     * @since 2.0.0
     */
    protected $_addressConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerMetadataInterface $customerMetadataService
     * @param AddressMetadataInterface $addressMetadataService
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerMetadataInterface $customerMetadataService,
        AddressMetadataInterface $addressMetadataService,
        \Magento\Customer\Model\Address\Config $addressConfig
    ) {
        $this->_blockFactory = $blockFactory;
        $this->_storeManager = $storeManager;
        $this->_customerMetadataService = $customerMetadataService;
        $this->_addressMetadataService = $addressMetadataService;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context);
    }

    /**
     * Addresses url
     *
     * @return void
     * @since 2.0.0
     */
    public function getBookUrl()
    {
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function getEditUrl()
    {
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function getDeleteUrl()
    {
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function getCreateUrl()
    {
    }

    /**
     * @param string $renderer
     * @return \Magento\Framework\View\Element\BlockInterface
     * @since 2.0.0
     */
    public function getRenderer($renderer)
    {
        if (is_string($renderer) && $renderer) {
            return $this->_blockFactory->createBlock($renderer, []);
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
     * @since 2.0.0
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();
        if (!isset($this->_config[$websiteId])) {
            $this->_config[$websiteId] = $this->scopeConfig->getValue(
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAttributeValidationClass($attributeCode)
    {
        $class = '';

        try {
            /** @var $attribute AttributeMetadataInterface */
            $attribute = isset($this->_attributes[$attributeCode])
                ? $this->_attributes[$attributeCode]
                : $this->_addressMetadataService->getAttributeMetadata($attributeCode);

            $class = $attribute ? $attribute->getFrontendClass() : '';
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
     * @since 2.0.0
     */
    public function convertStreetLines($origStreets, $toCount)
    {
        $lines = [];
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
     * @since 2.0.0
     */
    public function isVatValidationEnabled($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_VAT_VALIDATION_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve disable auto group assign default value
     *
     * @return bool
     * @since 2.0.0
     */
    public function isDisableAutoGroupAssignDefaultValue()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve 'validate on each transaction' value
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return bool
     * @since 2.0.0
     */
    public function hasValidateOnEachTransaction($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
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
     * @since 2.0.0
     */
    public function getTaxCalculationAddressType($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_VIV_TAX_CALCULATION_ADDRESS_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if VAT ID address attribute has to be shown on frontend (on Customer Address management forms)
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isVatAttributeVisible()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_VAT_FRONTEND_VISIBILITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve attribute visibility
     *
     * @param string $code
     * @return bool
     * @since 2.2.0
     */
    public function isAttributeVisible($code)
    {
        $attributeMetadata = $this->_addressMetadataService->getAttributeMetadata($code);
        if ($attributeMetadata) {
            return $attributeMetadata->isVisible();
        }
        return false;
    }
}
