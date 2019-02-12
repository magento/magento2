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

/**
 * Bulk configuration transfer resource model
 */
class BulkConfigurationTransfer
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

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
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
    }

    /**
     * Bulk transfer source items configurations from origin source to destination source
     *
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     */
    public function execute(
        array $skus,
        string $originSource,
        string $destinationSource
    ) {
        $tableName = $this->resourceConnection->getTableName('inventory_low_stock_notification_configuration');
        $connection = $this->resourceConnection->getConnection();

        $skuTypes = $this->getProductTypesBySkus->execute($skus);

        /**
         * We are filtering SKU list for products which are not allowed to move configuration for
         */
        foreach ($skuTypes as $sku => $type) {
            if (!$this->isSourceItemManagementAllowedForProductType->execute($type)) {
                unset($skuTypes[$sku]);
            }
        }

        $destinationItemsQuery = $connection
            ->select()
            ->from($tableName, ['sku', 'notify_stock_qty'])
            ->where('sku IN (?)', array_keys($skuTypes))
            ->where('source_code = ?', $destinationSource);
        $destinationQueryResult = $connection->fetchPairs($destinationItemsQuery);

        $skusForDestination = array_diff_key($skuTypes, $destinationQueryResult);
        if (!empty($skusForDestination)) {
            /**
             * Get configuration from DB to transfer
             */
            $allowedSkus = array_keys($skusForDestination);
            $sourceItemsQuery = $connection
                ->select()
                ->from($tableName, ['sku','notify_stock_qty'])
                ->where('sku IN (?)', $allowedSkus)
                ->where('source_code = ?', $originSource);
            $sourceQueryResult = $connection->fetchAll($sourceItemsQuery);

            /**
             * Collect information about items to be copied
             * and also create an array of items that are not presented in original source
             */
            $notPresentedSkus = $skusForDestination;
            $itemsAllowedToMove = [];
            foreach ($sourceQueryResult as $inventoryNotificationItem) {
                $inventoryNotificationItem['source_code'] = $destinationSource;
                $itemsAllowedToMove[] = $inventoryNotificationItem;
                unset($notPresentedSkus[$inventoryNotificationItem['sku']]);
            }

            foreach ($notPresentedSkus as $sku => $type) {
                $itemsAllowedToMove[] = [
                    'sku' => $sku,
                    'notify_stock_qty' => null,
                    'source_code' => $destinationSource,
                ];
            }

            $connection->insertMultiple(
                $tableName,
                $itemsAllowedToMove
            );
        }
    }
}
