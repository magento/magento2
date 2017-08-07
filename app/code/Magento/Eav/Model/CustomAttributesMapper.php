<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\MapperInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class CustomAttributesMapper
 * @since 2.2.0
 */
class CustomAttributesMapper implements MapperInterface
{
    /**
     * @var AttributeRepositoryInterface
     * @since 2.2.0
     */
    private $attributeRepository;

    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.2.0
     */
    private $searchCriteriaBuilder;

    /**
     * @var array
     * @since 2.2.0
     */
    private $attributes;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @since 2.2.0
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function entityToDatabase($entityType, $data)
    {
        if (!$this->metadataPool->hasConfiguration($entityType)
            || !$this->metadataPool->getMetadata($entityType)->getEavEntityType()
        ) {
            return $data;
        }
        if (isset($data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])) {
            foreach ($this->getNonStaticAttributes($entityType) as $attribute) {
                foreach ($data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] as $key => $customAttribute) {
                    if ($customAttribute[AttributeInterface::ATTRIBUTE_CODE] == $attribute->getAttributeCode()) {
                        unset($data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES][$key]);
                        $data[$attribute->getAttributeCode()] = $customAttribute[AttributeInterface::VALUE];
                    }
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function databaseToEntity($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        if (!$metadata->getEavEntityType()) {
            return $data;
        }
        foreach ($this->getNonStaticAttributes($entityType) as $attribute) {
            if (isset($data[$attribute->getAttributeCode()])) {
                $data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES][] = [
                    AttributeInterface::ATTRIBUTE_CODE => $attribute->getAttributeCode(),
                    AttributeInterface::VALUE => $data[$attribute->getAttributeCode()]
                ];
            }
        }
        return $data;
    }

    /**
     * Get custom attributes
     *
     * @param string $entityType
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @throws \Exception
     * @since 2.2.0
     */
    private function getNonStaticAttributes($entityType)
    {
        if (!isset($this->attributes[$entityType])) {
            $metadata = $this->metadataPool->getMetadata($entityType);
            $searchResult = $this->attributeRepository->getList(
                $metadata->getEavEntityType(),
                $this->searchCriteriaBuilder->addFilter('attribute_set_id', null, 'neq')->create()
            );
            $attributes = [];
            foreach ($searchResult->getItems() as $attribute) {
                if (!$attribute->isStatic()) {
                    $attributes[] = $attribute;
                }
            }
            $this->attributes[$entityType] = $attributes;
        }
        return $this->attributes[$entityType];
    }
}
