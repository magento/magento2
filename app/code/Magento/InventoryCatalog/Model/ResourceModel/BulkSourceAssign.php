<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Implementation of bulk source assignment
 *
 * This class is not intended to be used directly.
 * @see \Magento\InventoryCatalogApi\Api\BulkSourceAssignInterface
 */
class BulkSourceAssign
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
    }

    /**
     * Assign sources to products
     * @param array $skus
     * @param array $sourceCodes
     * @return int
     */
    public function execute(array $skus, array $sourceCodes): int
    {
        $types = $this->getProductTypesBySkus->execute($skus);

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        $count = 0;
        foreach ($types as $sku => $type) {
            if ($this->isSourceItemManagementAllowedForProductType->execute($type)) {
                foreach ($sourceCodes as $sourceCode) {
                    try {
                        $connection->insert($tableName, [
                            SourceItemInterface::SOURCE_CODE => $sourceCode,
                            SourceItemInterface::SKU => $sku,
                            SourceItemInterface::QUANTITY => 0,
                            SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
                        ]);
                        $count++;
                    } catch (DuplicateException $e) {
                        // Skip if source assignment is duplicated
                        continue;
                    }
                }
            }
        }

        return $count;
    }
}
