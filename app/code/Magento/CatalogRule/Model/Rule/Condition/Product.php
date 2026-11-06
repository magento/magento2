<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogRule\Model\Rule\Condition;

use Magento\Catalog\Model\Product\Attribute\Backend\Stock;

/**
 * Catalog Rule Product Condition data model
 *
 * @method string getAttribute() Returns attribute code
 */
class Product extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attrCode = $this->getAttribute();
        if ('category_ids' == $attrCode) {
            return parent::validate($model);
        }

        $oldAttrValue = $model->getData($attrCode);

        $this->_setAttributeValue($model);

        $stockStatus = $this->getStockStatus($model);
        if ($stockStatus !== null) {
            $model->setData($attrCode, $stockStatus);
        }

        $attrValue = $model->getData($attrCode);
        if ($attrValue === null) {
            if ($this->getOperator() === '<=>') {
                $this->_restoreOldAttrValue($model, $oldAttrValue);
                return true;
            }
            $this->_restoreOldAttrValue($model, $oldAttrValue);
            return false;
        }

        $result = $this->validateAttribute($attrValue);
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    /**
     * Get stock status of the product when the condition attribute is backed by the stock item
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Model\AbstractModel $model
     * @return int|null
     */
    private function getStockStatus(\Magento\Framework\Model\AbstractModel $model): ?int
    {
        $attrCode = $this->getAttribute();
        $attribute = $model->getResource() ? $model->getResource()->getAttribute($attrCode) : null;
        if (!$attribute || !is_a((string)$attribute->getBackendModel(), Stock::class, true)) {
            return null;
        }

        $stockModel = clone $model;
        $attribute->getBackend()->afterLoad($stockModel);
        $value = $stockModel->getData($attrCode);

        return is_array($value) && isset($value['is_in_stock']) ? (int)$value['is_in_stock'] : null;
    }

    /**
     * Restore old attribute value
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param mixed $oldAttrValue
     * @return void
     */
    protected function _restoreOldAttrValue(\Magento\Framework\Model\AbstractModel $model, $oldAttrValue)
    {
        $attrCode = $this->getAttribute();
        if ($oldAttrValue === null) {
            $model->unsetData($attrCode);
        } else {
            $model->setData($attrCode, $oldAttrValue);
        }
    }

    /**
     * Set attribute value
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Model\AbstractModel $model
     * @return $this
     */
    protected function _setAttributeValue(\Magento\Framework\Model\AbstractModel $model)
    {
        $storeId = $model->getStoreId();
        $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        if (!isset($this->_entityAttributeValues[$model->getId()])) {
            return $this;
        }

        $productValues  = $this->_entityAttributeValues[$model->getId()];

        if (!isset($productValues[$storeId]) && !isset($productValues[$defaultStoreId])) {
            return $this;
        }

        $value = isset($productValues[$storeId]) ? $productValues[$storeId] : $productValues[$defaultStoreId];

        $value = $this->_prepareDatetimeValue($value, $model);
        $value = $this->_prepareMultiselectValue($value, $model);

        $model->setData($this->getAttribute(), $value);

        return $this;
    }

    /**
     * Prepare datetime attribute value
     *
     * @param mixed $value
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Model\AbstractModel $model
     * @return mixed
     */
    protected function _prepareDatetimeValue($value, \Magento\Framework\Model\AbstractModel $model)
    {
        $attribute = $model->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getBackendType() == 'datetime') {
            if (!$value) {
                return null;
            }
            $this->setValue(strtotime($this->getValue()));
            $value = strtotime($value);
        }

        return $value;
    }

    /**
     * Prepare multiselect attribute value
     *
     * @param mixed $value
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Model\AbstractModel $model
     * @return mixed
     */
    protected function _prepareMultiselectValue($value, \Magento\Framework\Model\AbstractModel $model)
    {
        $attribute = $model->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getFrontendInput() == 'multiselect') {
            $value = strlen($value) ? explode(',', $value) : [];
        }

        return $value;
    }
}
