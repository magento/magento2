<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Get ids of all stores, for which the 'all store views' value is used for current attribute.
 */
class GetStoresWithDefaultValuesUsed
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TypeFactory $typeFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->typeFactory = $typeFactory;
    }

    /**
     * Get ids of all stores, for which the 'all store views' value is used for current attribute.
     *
     * @param string $attributeCode
     * @param int $entityId
     * @param string $entityType
     * @return int[]
     */
    public function execute(
        string $attributeCode,
        int $entityId,
        string $entityType
    ): array {
        try {
            $entityTypeId = (int)$this->typeFactory->create()->loadByCode($entityType)->getEntityTypeId();
            $result = $this->getEavAttributeData($attributeCode, $entityTypeId);
            $backendType = $result['backend_type'];
            $attributeId = (int)$result['attribute_id'];
            $result = ('static' !== $backendType) ?
                $this->getStoresWithDefaultValues($attributeId, $backendType, $entityId, $entityType) : [];
        } catch (\Exception $exception) {
            $result = [];
        }

        return $result;
    }

    /**
     * Get attribute id and backend type by attribute code and entity type id.
     *
     * @param string $attributeCode
     * @param int $entityTypeId
     * @return array
     */
    private function getEavAttributeData(string $attributeCode, int $entityTypeId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $eavAttributeTableName = $this->resourceConnection->getTableName('eav_attribute');
        $select = $connection->select()
            ->from(
                ['eav_attribute' => $eavAttributeTableName],
                ['backend_type', 'attribute_id']
            )->where(
                'eav_attribute.entity_type_id = ?',
                $entityTypeId
            )->where(
                'eav_attribute.attribute_code = ?',
                $attributeCode
            );

        return $connection->fetchRow($select);
    }

    /**
     * Get all store ids by attribute id, backend type, entity id and entity type.
     *
     * @param int $attributeId
     * @param string $backendType
     * @param int $entityId
     * @param string $entityType
     * @return array
     */
    private function getStoresWithDefaultValues(
        int $attributeId,
        string $backendType,
        int $entityId,
        string $entityType
    ): array {
        $connection = $this->resourceConnection->getConnection();
        $entityTable = $this->resourceConnection->getTableName($entityType . '_entity_' . $backendType);
        $storeViewTable = $this->resourceConnection->getTableName('store');
        $selectUsedStoreIds = $connection->select()
            ->from(
                ['entity_table' => $entityTable],
                ['store_id']
            )->where(
                'entity_table.entity_id = ?',
                $entityId
            )->where(
                'entity_table.attribute_id = ?',
                $attributeId
            );
        $selectUnusedStoreIds = $connection->select()
            ->from(
                ['store' => $storeViewTable],
                ['store_id']
            )->where(
                'store.store_id NOT IN (?)',
                new \Zend_Db_Expr($selectUsedStoreIds)
            );

        return $connection->fetchCol($selectUnusedStoreIds, 'store_id');
    }
}
