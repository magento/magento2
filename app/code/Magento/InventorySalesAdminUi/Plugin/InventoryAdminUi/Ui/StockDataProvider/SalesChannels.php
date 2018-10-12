<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Plugin\InventoryAdminUi\Ui\StockDataProvider;

use Magento\CatalogInventory\Model\Stock\StockRepository;
use Magento\InventoryAdminUi\Ui\DataProvider\StockDataProvider;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;

/**
 * Customize stock form. Add sales channels data
 */
class SalesChannels
{
    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $getAssignedSalesChannelsForStock;

    /**
     * @var StockRepository
     */
    private $stockRepository;

    /**
     * @param GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     * @param StockRepository $stockRepository
     */
    public function __construct(
        GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock,
        StockRepository $stockRepository
    ) {
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
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
                $salesChannelsData = $this->getSalesChannelsDataForStock($stockData['general']);
                if (count($salesChannelsData)) {
                    $stockData['sales_channels'] = $salesChannelsData;
                }
            }
            unset($stockData);
        } elseif (isset($data['totalRecords']) && $data['totalRecords'] > 0) {
            foreach ($data['items'] as &$stockData) {
                $salesChannelsData = $this->getSalesChannelsDataForStock($stockData);
                if (count($salesChannelsData)) {
                    $stockData['sales_channels'] = $salesChannelsData;
                }
            }
            unset($stockData);
        }
        return $data;
    }

    /**
     * @param array $stock
     * @return array
     */
    private function getSalesChannelsDataForStock(array $stock): array
    {
        $salesChannelsData = [];
        foreach ($stock['extension_attributes'] as $salesChannels) {
            foreach ($salesChannels as $salesChannel) {
                $salesChannelsData[$salesChannel['type']][] = $salesChannel['code'];
            }
        }
        return $salesChannelsData;
    }
}
