<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

/**
 * Backend model for attribute with multiple values
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class ArrayBackend extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Prepare data for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);
        if (is_array($data)) {
            $data = array_filter($data);
            $object->setData($attributeCode, implode(',', $data));
        }

        return parent::beforeSave($object);
    }

    /**
     * Implode data for validation
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return bool
     */
    public function validate($object)
    {
        $attribute = $this->getAttribute();
        $attributeCode = $attribute->getAttributeCode();
        $data = $object->getData($attributeCode);
        $assigned = $object->hasData($attributeCode);
        if (is_array($data)) {
            $object->setData($attributeCode, implode(',', array_filter($data)));
        } elseif (empty($data) && $assigned) {
            $object->setData($attributeCode, null);
        }

        return parent::validate($object);
    }
}
