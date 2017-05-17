<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Container;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Framework\App\ObjectManager;

class Attribute
{
    /**
     * @var string[]
     */
    private $idToCodeMap = [];

    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var EavAttribute[]
     */
    private $attributes;

    /**
     * @var EavAttribute[]
     */
    private $searchableAttributes;

    /**
     * @param Collection $attributeCollection
     * @param CollectionFactory $attributeCollectionFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) left $attributeCollection for BIC
     */
    public function __construct(Collection $attributeCollection, CollectionFactory $attributeCollectionFactory = null)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory ?:
            ObjectManager::getInstance()->get(CollectionFactory::class);
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
                : $this->attributeCollectionFactory->create()->getItemById($attributeId)->getAttributeCode();
            $this->idToCodeMap[$attributeId] = $code;
        }

        return $this->idToCodeMap[$attributeId];
    }

    /**
     * @param string $attributeCode
     * @return int
     * @deprecated This method does not used anymore
     */
    public function getAttributeIdByCode($attributeCode)
    {
        if (!array_key_exists($attributeCode, array_flip($this->idToCodeMap))) {
            $attributeId = $attributeCode === 'options'
                ? 'options'
                : $this->attributeCollectionFactory->create()
                    ->getItemByColumnValue('attribute_code', $attributeCode)->getId();
            $this->idToCodeMap[$attributeId] = $attributeCode;
        }
        $codeToIdMap = array_flip($this->idToCodeMap);
        return $codeToIdMap[$attributeCode];
    }

    /**
     * Get instance of product attribute for specified attribute code
     *
     * @param string $attributeCode
     * @return EavAttribute|null
     * @deprecated
     * @see self::getSearchableAttribute
     */
    public function getAttribute($attributeCode)
    {
        $searchableAttributes = $this->getAttributes();
        return array_key_exists($attributeCode, $searchableAttributes)
            ? $searchableAttributes[$attributeCode]
            : null;
    }

    /**
     * Get list of searchable product attributes
     *
     * @return \Magento\Catalog\Model\ResourceModel\Attribute[]
     */
    private function getSearchableAttributes()
    {
        if (null === $this->searchableAttributes) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributesCollection */
            $attributesCollection = $this->attributeCollectionFactory->create()->addToIndexFilter(true);

            foreach ($attributesCollection as $attribute) {
                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $this->searchableAttributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $this->searchableAttributes;
    }

    /**
     * Get instance of searchable product attribute for specified attribute code
     *
     * @param string $attributeCode
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute|null
     */
    public function getSearchableAttribute($attributeCode)
    {
        $searchableAttributes = $this->getSearchableAttributes();
        return array_key_exists($attributeCode, $searchableAttributes)
            ? $searchableAttributes[$attributeCode]
            : null;
    }

    /**
     * Get list of all product attributes
     *
     * @return EavAttribute[]
     * @deprecated
     * @see self::getSearchableAttributes
     */
    public function getAttributes()
    {
        if (null === $this->attributes) {
            foreach ($this->attributeCollectionFactory->create() as $attribute) {
                /** @var EavAttribute $attribute */
                $this->attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }
        return $this->attributes;
    }
}
