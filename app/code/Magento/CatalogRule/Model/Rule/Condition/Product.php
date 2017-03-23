<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Rule Product Condition data model
 */
namespace Magento\CatalogRule\Model\Rule\Condition;

/**
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
            return $this->validateAttribute($model->getAvailableInCategories());
        }

        $oldAttrValue = $model->getData($attrCode);
        if ($oldAttrValue === null) {
            return false;
        }

        $this->_setAttributeValue($model);

        $result = $this->validateAttribute($model->getData($attrCode));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
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
