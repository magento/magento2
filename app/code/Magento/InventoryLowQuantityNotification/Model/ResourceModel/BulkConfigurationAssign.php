<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration;

/**
 * Bulk configuration assign resource model
 */
class BulkConfigurationAssign
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
     * Bulk assign source items configurations to source items
     *
     * @param array $skus
     * @param array $sources
     */
    public function execute(
        array $skus,
        array $sources
    ) {
        $tableName = $this->resourceConnection->getTableName('inventory_low_stock_notification_configuration');
        $connection = $this->resourceConnection->getConnection();

        $types = $this->getProductTypesBySkus->execute($skus);

        foreach ($types as $sku => $type) {
            if ($this->isSourceItemManagementAllowedForProductType->execute($type)) {
                foreach ($sources as $sourceCode) {
                    try {
                        $connection->insert($tableName, [
                            SourceItemConfiguration::SOURCE_CODE => $sourceCode,
                            SourceItemConfiguration::INVENTORY_NOTIFY_QTY => null,
                            SourceItemConfiguration::SKU => $sku,
                        ]);
                    } catch (DuplicateException $e) {
                        // Skip if source assignment is duplicated
                        continue;
                    }
                }
            }
        }
    }
}
