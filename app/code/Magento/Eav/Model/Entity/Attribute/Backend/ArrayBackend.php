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
        if ($object->hasData($attributeCode)) {
            $data = $object->getData($attributeCode);
            if (is_array($data)) {
                $object->setData($attributeCode, $this->prepare($data));
            } elseif (is_string($data)) {
                $object->setData($attributeCode, $this->prepare(explode(',', $data)));
            } elseif (empty($data)) {
                $object->setData($attributeCode, null);
            }
        }

        return parent::validate($object);
    }

    /**
     * Prepare attribute values
     *
     * @param array $data
     * @return string
     */
    private function prepare(array $data): string
    {
        return implode(
            ',',
            array_filter(
                array_unique($data),
                fn($value) => is_numeric($value) || !empty($value)
            )
        );
    }
}
