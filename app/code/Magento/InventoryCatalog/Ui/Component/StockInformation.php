<?php

namespace Magento\InventoryCatalog\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\GetSalesChannelToStockDataInterface;
use Magento\Ui\Component\Container;

class StockInformation extends Container
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var GetProductQuantityInStockInterface
     */
    private $getProductQuantityInStock;

    /**
     * @var GetSalesChannelToStockDataInterface
     */
    private $salesChannelToStockData;

    /**
     * @param ContextInterface $context
     * @param StockRepositoryInterface $stockRepository
     * @param GetSalesChannelToStockDataInterface $salesChannelToStockData
     * @param GetProductQuantityInStockInterface $getProductQuantityInStock
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StockRepositoryInterface $stockRepository,
        GetSalesChannelToStockDataInterface $salesChannelToStockData,
        GetProductQuantityInStockInterface $getProductQuantityInStock,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->stockRepository = $stockRepository;
        $this->salesChannelToStockData = $salesChannelToStockData;
        $this->getProductQuantityInStock = $getProductQuantityInStock;
    }

    /**
     * @param $productSku string
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getActiveStocksQtyInfo($productSku): array
    {
        $salesChannelToStockIds = $this->salesChannelToStockData->execute();
        $stockInfo = [];
        if ($salesChannelToStockIds) {
            foreach ($salesChannelToStockIds as $stock) {
                $stockId = $stock['stock_id'];
                if ($stockId) {
                    $currentStock = [];
                    $stockData = $this->stockRepository->get($stockId);
                    $currentStock['stock_name'] = $stockData->getName();
                    $currentStock['qty'] = $this->getProductQuantityInStock->execute($productSku, $stockId);
                    $stockInfo[] = $currentStock;
                    unset($currentStock);
                }
            }
        }
        return $stockInfo;
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource): array
    {
        $productSku = $dataSource['data']['product']['sku'];
        if (!$productSku) {
            return $dataSource;
        }
        $activeStocksInfo = $this->getActiveStocksQtyInfo($productSku);
        if ($activeStocksInfo) {
            foreach ($activeStocksInfo as $stockData) {
                $dataSource['data']['stocks'][] = $stockData;
            }
        }
        return $dataSource;
    }
}
