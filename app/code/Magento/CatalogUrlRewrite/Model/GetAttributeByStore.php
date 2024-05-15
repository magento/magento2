<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\ResourceModel\AttributeValue;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

class GetAttributeByStore
{

    /**
     * @param AttributeValue $attributeValue
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        private readonly AttributeValue $attributeValue,
        private readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * Get attribute values by store
     *
     * @param ProductInterface|CategoryInterface $entity
     * @param string $attributeCode
     * @param array $storeIds
     * @return array
     */
    public function execute(
        ProductInterface|CategoryInterface $entity,
        string $attributeCode,
        array $storeIds = [Store::DEFAULT_STORE_ID]
    ): array {
        $storeIds = array_merge($entity->getStoreIds(), $storeIds);
        $entityType = $entity instanceof CategoryInterface ?
            CategoryInterface::class : ProductInterface::class;

        try {
            $metadata = $this->metadataPool->getMetadata($entityType);
            $attributeRows = $this->attributeValue->getValues(
                $entityType,
                (int)$entity->getData($metadata->getLinkField()),
                [$attributeCode],
                $storeIds
            );
        } catch (\Exception) {
            $attributeRows = [];
        }

        $attributeByStore = [];
        foreach ($attributeRows as $row) {
            $attributeByStore[$row['store_id']] = $row['value'];
        }
        return $attributeByStore;
    }
}
