<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

/**
 * Implementation of bulk source assignment
 *
 * This class is not intended to be used directly.
 * @see \Magento\InventoryCatalogApi\Api\BulkSourceUnassignInterface
 */
class BulkSourceUnassign
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var BulkZeroLegacyStockItem
     */
    private $bulkZeroLegacyStockItem;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param BulkZeroLegacyStockItem $bulkZeroLegacyStockItem
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider,
        BulkZeroLegacyStockItem $bulkZeroLegacyStockItem
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->bulkZeroLegacyStockItem = $bulkZeroLegacyStockItem;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * Assign sources to products
     * @param array $skus
     * @param array $sourceCodes
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(array $skus, array $sourceCodes): int
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        $connection->beginTransaction();

        $count = (int) $connection->delete($tableName, [
            SourceItemInterface::SOURCE_CODE . ' IN (?)' => $sourceCodes,
            SourceItemInterface::SKU . ' IN (?)' => $skus,
        ]);

        // Legacy stock update
        if (in_array($this->defaultSourceProvider->getCode(), $sourceCodes)) {
            $this->bulkZeroLegacyStockItem->execute($skus);
        }

        $connection->commit();

        return $count;
    }
}
