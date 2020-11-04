<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

/**
 * Backend model for attribute with multiple values
 *
 * @api
 * @since 100.0.2
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
            $data = array_filter($data, function ($value) {
                return $value === '0' || !empty($value);
            });
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
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);
        if (is_array($data)) {
            $object->setData($attributeCode, implode(',', array_filter($data)));
        } elseif (empty($data)) {
            $object->setData($attributeCode, null);
        }
        return parent::validate($object);
    }
}
