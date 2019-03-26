<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product tier price backend attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;

/**
 * Backend model for Tierprice attribute
 */
class Tierprice extends \Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice
{
    /**
     * Catalog product attribute backend tierprice
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    protected $_productAttributeBackendTierprice;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $productAttributeTierprice
     * @param ScopeOverriddenValue|null $scopeOverriddenValue
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice $productAttributeTierprice,
        ScopeOverriddenValue $scopeOverriddenValue = null
    ) {
        $this->_productAttributeBackendTierprice = $productAttributeTierprice;
        parent::__construct(
            $currencyFactory,
            $storeManager,
            $catalogData,
            $config,
            $localeFormat,
            $catalogProductType,
            $groupManagement,
            $scopeOverriddenValue
        );
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice
     */
    protected function _getResource()
    {
        return $this->_productAttributeBackendTierprice;
    }

    /**
     * Add price qty to unique fields
     *
     * @param array $objectArray
     * @return array
     */
    protected function _getAdditionalUniqueFields($objectArray)
    {
        $uniqueFields = parent::_getAdditionalUniqueFields($objectArray);
        $uniqueFields['qty'] = $objectArray['price_qty'] * 1;
        return $uniqueFields;
    }

    /**
     * @inheritdoc
     */
    protected function getAdditionalFields($objectArray)
    {
        $percentageValue = $this->getPercentage($objectArray);
        return [
            'value' => $percentageValue ? null : $objectArray['price'],
            'percentage_value' => $percentageValue ?: null,
        ];
    }

    /**
     * Error message when duplicates
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getDuplicateErrorMessage()
    {
        return __('We found a duplicate website, tier price, customer group and quantity.');
    }

    /**
     * Whether tier price value fixed or percent of original price
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $priceObject
     * @return bool
     */
    protected function _isPriceFixed($priceObject)
    {
        return $priceObject->isTierPriceFixed();
    }

    /**
     * By default attribute value is considered non-scalar that can be stored in a generic way
     *
     * @return bool
     */
    public function isScalar()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function validate($object)
    {
        $attribute = $this->getAttribute();
        $priceRows = $object->getData($attribute->getName());
        $priceRows = array_filter((array)$priceRows);

        foreach ($priceRows as $priceRow) {
            $percentage = $this->getPercentage($priceRow);
            if ($percentage !== null && (!$this->isPositiveOrZero($percentage) || $percentage > 100)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Percentage value must be a number between 0 and 100.')
                );
            }
        }

        return parent::validate($object);
    }

    /**
     * @inheritdoc
     */
    protected function validatePrice(array $priceRow)
    {
        if (!$this->getPercentage($priceRow)) {
            parent::validatePrice($priceRow);
        }
    }

    /**
     * @inheritdoc
     */
    protected function modifyPriceData($object, $data)
    {
        /** @var \Magento\Catalog\Model\Product $object */
        $data = parent::modifyPriceData($object, $data);
        $price = $object->getPrice();
        foreach ($data as $key => $tierPrice) {
            $percentageValue = $this->getPercentage($tierPrice);
            if ($percentageValue) {
                $data[$key]['price'] = $price * (1 - $percentageValue / 100);
                $data[$key]['website_price'] = $data[$key]['price'];
            }
        }
        return $data;
    }

    /**
     * Update Price values in DB
     *
     * Updates price values in DB from array comparing to old values. Returns bool if updated
     *
     * @param array $valuesToUpdate
     * @param array $oldValues
     * @return boolean
     */
    protected function updateValues(array $valuesToUpdate, array $oldValues)
    {
        $isChanged = false;
        foreach ($valuesToUpdate as $key => $value) {
            if ((!empty($value['value']) && $oldValues[$key]['price'] != $value['value'])
                || $this->getPercentage($oldValues[$key]) != $this->getPercentage($value)
            ) {
                $price = new \Magento\Framework\DataObject(
                    [
                        'value_id' => $oldValues[$key]['price_id'],
                        'value' => $value['value'],
                        'percentage_value' => $this->getPercentage($value)
                    ]
                );
                $this->_getResource()->savePriceData($price);

                $isChanged = true;
            }
        }
        return $isChanged;
    }

    /**
     * Check whether price has percentage value.
     *
     * @param array $priceRow
     * @return null
     */
    private function getPercentage($priceRow)
    {
        return isset($priceRow['percentage_value']) && is_numeric($priceRow['percentage_value'])
            ? $priceRow['percentage_value']
            : null;
    }
}
