<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Plugin\Model\ResourceModel;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\ReadSnapshot;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Extend Eav ReadSnapshot by adding data from product or category attributes with global scope.
 * Default ReadSnapshot returns only data for current scope where entity is editing, but attributes with global scope,
 * e.g. price, is written only to default scope (store_id = 0) in case Catalog Price Scope set to "Global"
 */
class ReadSnapshotPlugin
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var EavConfig
     */
    private $config;

    /**
     * @param MetadataPool $metadataPool
     * @param EavConfig $config
     */
    public function __construct(
        MetadataPool $metadataPool,
        EavConfig $config
    ) {
        $this->metadataPool = $metadataPool;
        $this->config = $config;
    }

    /**
     * @param ReadSnapshot $subject
     * @param array $entityData
     * @param string $entityType
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(ReadSnapshot $subject, array $entityData, $entityType)
    {
        if (!in_array($entityType, [ProductInterface::class, CategoryInterface::class], true)) {
            return $entityData;
        }

        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();
        $globalAttributes = [];
        $attributesMap = [];
        $eavEntityType = $metadata->getEavEntityType();
        $attributes = null === $eavEntityType
            ? []
            : $this->config->getEntityAttributes($eavEntityType, new \Magento\Framework\DataObject($entityData));

        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (!$attribute->isStatic() && $attribute->isScopeGlobal()) {
                $globalAttributes[$attribute->getBackend()->getTable()][] = $attribute->getAttributeId();
                $attributesMap[$attribute->getAttributeId()] = $attribute->getAttributeCode();
            }
        }

        if ($globalAttributes) {
            $selects = [];
            foreach ($globalAttributes as $table => $attributeIds) {
                $select = $connection->select()
                    ->from(
                        ['t' => $table],
                        ['value' => 't.value', 'attribute_id' => 't.attribute_id']
                    )
                    ->where($metadata->getLinkField() . ' = ?', $entityData[$metadata->getLinkField()])
                    ->where('attribute_id' . ' in (?)', $attributeIds)
                    ->where('store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
                $selects[] = $select;
            }
            $unionSelect = new \Magento\Framework\DB\Sql\UnionExpression(
                $selects,
                \Magento\Framework\DB\Select::SQL_UNION_ALL
            );
            foreach ($connection->fetchAll($unionSelect) as $attributeValue) {
                $entityData[$attributesMap[$attributeValue['attribute_id']]] = $attributeValue['value'];
            }
        }

        return $entityData;
    }
}
