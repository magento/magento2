<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Container;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;

/**
 * @deprecated 2.2.0
 * This class is used only in deprecated \Magento\Elasticsearch\Model\Adapter\DataMapper\ProductDataMapper
 * and must not be used for new code
 * @since 2.1.0
 */
class Attribute
{
    /**
     * @var string[]
     * @since 2.1.0
     */
    private $idToCodeMap = [];

    /**
     * @var Collection
     * @since 2.1.0
     */
    private $attributeCollection;

    /**
     * @var EavAttribute[]
     * @since 2.1.0
     */
    private $attributes = [];

    /**
     * @param Collection $attributeCollection
     * @since 2.1.0
     */
    public function __construct(Collection $attributeCollection)
    {
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * @param int $attributeId
     * @return string
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @return EavAttribute|null
     * @since 2.1.0
     */
    public function getAttribute($attributeCode)
    {
        $searchableAttributes = $this->getAttributes();
        return array_key_exists($attributeCode, $searchableAttributes)
            ? $searchableAttributes[$attributeCode]
            : null;
    }

    /**
     * @return EavAttribute[]
     * @since 2.1.0
     */
    public function getAttributes()
    {
        if (0 === count($this->attributes)) {
            /** @var Collection $attributesCollection */
            $attributesCollection = $this->attributeCollection;
            foreach ($attributesCollection as $attribute) {
                /** @var EavAttribute $attribute */
                $this->attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }
        return $this->attributes;
    }
}
