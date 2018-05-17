<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Indexer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\CatalogInventory\Model\Stock;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;

/**
 * Class for filter product price index.
 */
class ProductPriceIndexFilter implements PriceModifierInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var Status
     */
    private $stockStatus;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param Status $stockStatus
     */
    public function __construct(StockConfigurationInterface $stockConfiguration, Status $stockStatus)
    {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockStatus = $stockStatus;
    }

    /**
     * Remove out of stock products data from price index.
     *
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = [])
    {
        if ($this->stockConfiguration->isShowOutOfStock()) {
            return;
        }

        $connection = $this->stockStatus->getConnection();
        $select = $connection->select();
        $select->from(
            ['price_index' => $priceTable->getTableName()],
            ''
        );
        $select->joinLeft(
            ['website_stock' => $this->stockStatus->getMainTable()],
            'website_stock.product_id = price_index.' . $priceTable->getEntityField()
            . ' AND website_stock.website_id = price_index.' . $priceTable->getWebsiteField()
            . ' AND website_stock.stock_id = ' . Stock::DEFAULT_STOCK_ID,
            ''
        );
        $select->joinLeft(
            ['default_stock' => $this->stockStatus->getMainTable()],
            'default_stock.product_id = price_index.' . $priceTable->getEntityField()
            . ' AND default_stock.website_id = 0'
            . ' AND default_stock.stock_id = ' . Stock::DEFAULT_STOCK_ID,
            ''
        );
        $stockStatus = $connection->getIfNullSql('website_stock.stock_status', 'default_stock.stock_status');
        $select->where($stockStatus . ' = ?', Stock::STOCK_OUT_OF_STOCK);

        $query = $select->deleteFromSelect('price_index');
        $connection->query($query);
    }
}
