<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Inventory\Ui\StockDataProvider;

use Magento\CatalogInventory\Model\Stock\StockRepository;
use Magento\Inventory\Ui\DataProvider\StockDataProvider;
use Magento\InventorySales\Model\GetAssignedSalesChannelsForStockInterface;

/**
 * Customize stock form. Add sales channels data
 */
class SalesChannels
{
    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */

    private $channelsByStock;

    /**
     * @var StockRepository
     */
    private $stockRepository;

    /**
     * SalesChannels constructor.
     * @param GetAssignedSalesChannelsForStockInterface $channelsByStock
     * @param StockRepository $stockRepository
     */
    public function __construct(
        GetAssignedSalesChannelsForStockInterface $channelsByStock,
        StockRepository $stockRepository
    ) {
        $this->channelsByStock = $channelsByStock;
        $this->stockRepository = $stockRepository;
    }

    /**
     * @param StockDataProvider $subject
     * @param array $data
     * @return array
     */
    public function afterGetData(StockDataProvider $subject, array $data): array
    {
        if ('inventory_stock_form_data_source' === $subject->getName()) {
            foreach ($data as &$stockData) {
                $stockData['sales_channels'] = $this->getSalesChannelsDataForStock($data);
            }
            unset($stockData);
        } elseif ($data['totalRecords'] > 0) {
            foreach ($data['items'] as &$stockData) {
                $stockData['sales_channels'] = $this->getSalesChannelsDataForStock($data);
            }
            unset($stockData);
        }
        return $data;
    }

    /**
     * @param bool $data
     * @return array
     */
    private function getSalesChannelsDataForStock($data): array
    {
        $stockData = [];
        foreach ($data['items'] as $stock) {
            foreach ($stock['extension_attributes'] as $salesChannels) {
                foreach ($salesChannels as $salesChannel) {
                    $stockData[$salesChannel['type']][] = $salesChannel['code'];
                }
            }
        }
        return $stockData;
    }
}
