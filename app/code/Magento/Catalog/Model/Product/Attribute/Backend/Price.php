<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

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
     * Construct
     *
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->_currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_helper = $catalogData;
        $this->_config = $config;
    }

    /**
     * Set Attribute instance
     * Rewrite for redefine attribute scope
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
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
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @return $this
     */
    public function setScope($attribute)
    {
        if ($this->_helper->isPriceGlobal()) {
            $attribute->setIsGlobal(\Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL);
        } else {
            $attribute->setIsGlobal(\Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE);
        }

        return $this;
    }

    /**
     * After Save Attribute manipulation
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());
        /**
         * Orig value is only for existing objects
         */
        $oridData = $object->getOrigData();
        $origValueExist = $oridData && array_key_exists($this->getAttribute()->getAttributeCode(), $oridData);
        if ($object->getStoreId() != 0 || !$value || $origValueExist) {
            return $this;
        }

        if ($this->getAttribute()->getIsGlobal() == \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE) {
            $baseCurrency = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                'default'
            );

            $storeIds = $object->getStoreIds();
            if (is_array($storeIds)) {
                foreach ($storeIds as $storeId) {
                    $storeCurrency = $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
                    if ($storeCurrency == $baseCurrency) {
                        continue;
                    }
                    $rate = $this->_currencyFactory->create()->load($baseCurrency)->getRate($storeCurrency);
                    if (!$rate) {
                        $rate = 1;
                    }
                    $newValue = $value * $rate;
                    $object->addAttributeUpdate($this->getAttribute()->getAttributeCode(), $newValue, $storeId);
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
     * Returns whether the value has a valid price format and is greater than, or equal to, zero
     *
     * @param mixed $value
     * @return bool
     */
    protected function isPositiveOrZero($value)
    {
        $value = $this->standardizePrice($value);

        $hasValidFormat = preg_match('/^\d*(\.|,)?\d{0,4}$/i', $value);
        $isNegative = $value < 0;

        return  $hasValidFormat && !$isNegative;
    }

    /**
     * Attempt to return the value in a standardized way.
     * ex: 1.234,56 -> 1234.56
     * ex: 1,234.56 -> 1234.56
     *
     * @param mixed $value
     * @return mixed
     */
    protected function standardizePrice($value)
    {
        // based on the parseNumber() function in our validate.js script

        $value = str_replace(' ', '', $value);
        $iDot = strpos($value, '.');
        $iComma = strpos($value, ',');

        if ($iDot && $iComma) {
            if ($iComma > $iDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($iComma) {
            $value = str_replace(',', '.', $value);
        }

        return $value;
    }
}
