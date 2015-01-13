<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

class Config extends \Magento\Framework\Config\Data
{
    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = "eav_attributes"
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    /**
     * Retrieve list of locked fields for attribute
     *
     * @param AbstractAttribute $attribute
     * @return array
     */
    public function getLockedFields(AbstractAttribute $attribute)
    {
        $allFields = $this->get(
            $attribute->getEntityType()->getEntityTypeCode() . '/attributes/' . $attribute->getAttributeCode()
        );

        if (!is_array($allFields)) {
            return [];
        }
        $lockedFields = [];
        foreach (array_keys($allFields) as $fieldCode) {
            $lockedFields[$fieldCode] = $fieldCode;
        }

        return $lockedFields;
    }

    /**
     * Retrieve attributes list with config for entity
     *
     * @param string $entityCode
     * @return array
     */
    public function getEntityAttributesLockedFields($entityCode)
    {
        $lockedFields = [];

        $entityAttributes = $this->get($entityCode . '/attributes');
        foreach ($entityAttributes as $attributeCode => $attributeData) {
            foreach ($attributeData as $attributeField) {
                if ($attributeField['locked']) {
                    $lockedFields[$attributeCode][] = $attributeField['code'];
                }
            }
        }

        return $lockedFields;
    }
}
