<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

/**
 * Get all stocks Ids by sku
 */
class GetAssignedStockIdsBySku
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $sourceItemTableName;

    /**
     * @var string
     */
    private $stockSourceLinkTableName;

    /**
     * @param ResourceConnection $resource
     * @param string $sourceItemTableName
     * @param string $stockSourceLinkTableName
     */
    public function __construct(
        ResourceConnection $resource,
        string $sourceItemTableName,
        string $stockSourceLinkTableName
    ) {
        $this->resource = $resource;
        $this->sourceItemTableName = $sourceItemTableName;
        $this->stockSourceLinkTableName = $stockSourceLinkTableName;
    }

    /**
     * @param string $sku
     * @return array
     */
    public function execute(string $sku): array
    {
        $connection = $this->resource->getConnection();
        $sourceItemTable = $this->resource->getTableName($this->sourceItemTableName);
        $stockSourceLinkTable = $this->resource->getTableName($this->stockSourceLinkTableName);

        $select = $connection->select()
            ->from(
                ['source_item' => $sourceItemTable],
                []
            )->join(
                ['stock_source_link' => $stockSourceLinkTable],
                'source_item.'. SourceItemInterface::SOURCE_CODE .' = stock_source_link.'
                . StockSourceLinkInterface::SOURCE_CODE,
                [StockSourceLinkInterface::STOCK_ID]
            )->where(
                'source_item.' . SourceItemInterface::SKU . ' = ?',
                $sku
            )->distinct(true);

        return $connection->fetchCol($select);
    }
}
