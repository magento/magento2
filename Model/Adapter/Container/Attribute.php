<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Container;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

class Attribute
{
    /**
     * @var string[]
     */
    private $idToCodeMap = [];

    /**
     * @var Collection
     */
    private $attributeCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute[]
     */
    private $attributes = [];

    /**
     * @param Collection $attributeCollection
     */
    public function __construct(Collection $attributeCollection)
    {
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * @param int $attributeId
     * @return string
     */
    public function getAttributeCodeById($attributeId)
    {
        if (!array_key_exists($attributeId, $this->idToCodeMap)) {
            $code = $attributeId === 'options'
                ? 'options'
                : $this->attributeCollection->getItemById($attributeId)->getAttributeCode();
            $this->idToCodeMap[$attributeId] = $code;
        }
        return $this->idToCodeMap[$attributeId];
    }

    /**
     * @param string $attributeCode
     * @return int
     */
    public function getAttributeIdByCode($attributeCode)
    {
        if (!array_key_exists($attributeCode, array_flip($this->idToCodeMap))) {
            $attributeId = $attributeCode === 'options'
                ? 'options'
                : $this->attributeCollection->getItemByColumnValue('attribute_code', $attributeCode)->getId();
            $this->idToCodeMap[$attributeId] = $attributeCode;
        }
        $codeToIdMap = array_flip($this->idToCodeMap);
        return $codeToIdMap[$attributeCode];
    }

    /**
     * @param string $attributeCode
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute|null
     */
    public function getAttribute($attributeCode)
    {
        $searchableAttributes = $this->getAttributes();
        return array_key_exists($attributeCode, $searchableAttributes)
            ? $searchableAttributes[$attributeCode]
            : null;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Attribute[]
     */
    public function getAttributes()
    {
        if (0 === count($this->attributes)) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributesCollection */
            $attributesCollection = $this->attributeCollection;

            foreach ($attributesCollection as $attribute) {
                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $this->attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $this->attributes;
    }
}
