<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\DuplicateException;

class BulkConfigurationTransfer
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     * @return void
     */
    private function reassignConfigurations(
        array $skus,
        string $originSource,
        string $destinationSource
    ): void {
        $tableName = $this->resourceConnection->getTableName('inventory_low_stock_notification_configuration');
        $connection = $this->resourceConnection->getConnection();

        $connection->update(
            $tableName,
            ['source_code' => $destinationSource],
            $connection->quoteInto('sku IN (?)', $skus) . ' AND ' .
            $connection->quoteInto('source_code = ?', $originSource)
        );
    }

    /**
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     * @param bool $unassignFromOrigin
     */
    public function execute(
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignFromOrigin
    ) {
        $tableName = $this->resourceConnection->getTableName('inventory_low_stock_notification_configuration');
        $connection = $this->resourceConnection->getConnection();

        if ($unassignFromOrigin) {
            $this->reassignConfigurations($skus, $originSource, $destinationSource);
        } else {
            foreach ($skus as $sku) {
                $qry = $connection
                    ->select()
                    ->from($tableName, 'notify_stock_qty')
                    ->where('sku = ?', $sku)
                    ->where('source_code = ?', $originSource);

                $res = $connection->fetchOne($qry);

                $notifyStockQty = $res === null ? null : (float) $res;
                try {
                    $connection->insert(
                        $tableName,
                        [
                            'source_code' => $destinationSource,
                            'sku' => $sku,
                            'notify_stock_qty' => $notifyStockQty,
                        ]
                    );
                } catch (DuplicateException $e) {
                    $connection->update(
                        $tableName,
                        ['notify_stock_qty' => $notifyStockQty],
                        $connection->quoteInto('sku IN (?)', $skus) . ' AND ' .
                        $connection->quoteInto('source_code = ?', $destinationSource)
                    );
                }
            }
        }
    }
}
