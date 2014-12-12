<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Catalog Rule Product Condition data model
 */
namespace Magento\CatalogRule\Model\Rule\Condition;

class Product extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Framework\Object $object
     * @return bool
     */
    public function validate(\Magento\Framework\Object $object)
    {
        $attrCode = $this->getAttribute();
        if ('category_ids' == $attrCode) {
            return $this->validateAttribute($object->getAvailableInCategories());
        }

        $oldAttrValue = $object->hasData($attrCode) ? $object->getData($attrCode) : null;
        $this->_setAttributeValue($object);

        $result = $this->validateAttribute($object->getData($this->getAttribute()));
        $this->_restoreOldAttrValue($object, $oldAttrValue);

        return (bool)$result;
    }

    /**
     * Restore old attribute value
     *
     * @param \Magento\Framework\Object $object
     * @param mixed $oldAttrValue
     * @return void
     */
    protected function _restoreOldAttrValue($object, $oldAttrValue)
    {
        $attrCode = $this->getAttribute();
        if (is_null($oldAttrValue)) {
            $object->unsetData($attrCode);
        } else {
            $object->setData($attrCode, $oldAttrValue);
        }
    }

    /**
     * Set attribute value
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    protected function _setAttributeValue($object)
    {
        $storeId = $object->getStoreId();
        $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        if (!isset($this->_entityAttributeValues[$object->getId()])) {
            return $this;
        }

        $productValues  = $this->_entityAttributeValues[$object->getId()];

        if (!isset($productValues[$storeId]) && !isset($productValues[$defaultStoreId])) {
            return $this;
        }

        $value = isset($productValues[$storeId]) ? $productValues[$storeId] : $productValues[$defaultStoreId];

        $value = $this->_prepareDatetimeValue($value, $object);
        $value = $this->_prepareMultiselectValue($value, $object);

        $object->setData($this->getAttribute(), $value);
        return $this;
    }

    /**
     * Prepare datetime attribute value
     *
     * @param mixed $value
     * @param \Magento\Framework\Object $object
     * @return mixed
     */
    protected function _prepareDatetimeValue($value, $object)
    {
        $attribute = $object->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getBackendType() == 'datetime') {
            $value = strtotime($value);
        }
        return $value;
    }

    /**
     * Prepare multiselect attribute value
     *
     * @param mixed $value
     * @param \Magento\Framework\Object $object
     * @return mixed
     */
    protected function _prepareMultiselectValue($value, $object)
    {
        $attribute = $object->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getFrontendInput() == 'multiselect') {
            $value = strlen($value) ? explode(',', $value) : [];
        }
        return $value;
    }
}
