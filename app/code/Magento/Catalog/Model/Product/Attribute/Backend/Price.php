<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Catalog product price attribute backend model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Price extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Currency factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Core config model
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * Construct
     *
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Locale\FormatInterface $localeFormat
    ) {
        $this->_currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_helper = $catalogData;
        $this->_config = $config;
        $this->localeFormat = $localeFormat;
    }

    /**
     * Set Attribute instance
     * Rewrite for redefine attribute scope
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return $this
     */
    public function setAttribute($attribute)
    {
        parent::setAttribute($attribute);
        $this->setScope($attribute);
        return $this;
    }

    /**
     * Redefine Attribute scope
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return $this
     */
    public function setScope($attribute)
    {
        if ($this->_helper->isPriceGlobal()) {
            $attribute->setIsGlobal(ScopedAttributeInterface::SCOPE_GLOBAL);
        } else {
            $attribute->setIsGlobal(ScopedAttributeInterface::SCOPE_WEBSITE);
        }

        return $this;
    }

    /**
     * After Save Price Attribute manipulation
     * Processes product price attributes if price scoped to website and updates data when:
     * * Price changed for non-default store view - will update price for all stores assigned to current website.
     * * Price will be changed according to store currency even if price changed in product with default store id.
     * * In a case when price was removed for non-default store (use default option checked) the default store price
     * * will be used instead
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     */
    public function afterSave($object)
    {
        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = $this->getAttribute();
        $attributeCode = $attribute->getAttributeCode();
        $value = $object->getData($attributeCode);
        if ($value && $value != $object->getOrigData($attributeCode)) {
            if ($attribute->isScopeWebsite()) {
                foreach ((array)$object->getWebsiteStoreIds() as $storeId) {
                    /** @var $object \Magento\Catalog\Model\Product */
                    $object->addAttributeUpdate($attributeCode, $value, $storeId);
                }
            }
        }

        return $this;
    }

    /**
     * Validate
     *
     * @param \Magento\Catalog\Model\Product $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function validate($object)
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());
        if (empty($value)) {
            return parent::validate($object);
        }

        if (!$this->isPositiveOrZero($value)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please enter a number 0 or greater in this field.')
            );
        }

        return true;
    }

    /**
     * Returns whether the value is greater than, or equal to, zero
     *
     * @param mixed $value
     * @return bool
     */
    protected function isPositiveOrZero($value)
    {
        $value = $this->localeFormat->getNumber($value);
        $isNegative = $value < 0;
        return  !$isNegative;
    }
}
