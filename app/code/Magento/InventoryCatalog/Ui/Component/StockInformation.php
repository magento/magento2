<?php

namespace Magento\InventoryCatalog\Ui\Component;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\GetSalesChannelToStockDataInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Ui\Component\Container;

class StockInformation extends Container
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GetProductQuantityInStockInterface
     */
    private $getProductQuantityInStock;

    /**
     * @var DataObject
     */
    private $stocksInfo;

    /**
     * @var GetSalesChannelToStockDataInterface
     */
    private $salesChannelToStockData;

    public function __construct(
        ContextInterface $context,
        StockRepositoryInterface $stockRepository,
        GetSalesChannelToStockDataInterface $salesChannelToStockData,
        ProductRepositoryInterface $productRepository,
        GetProductQuantityInStockInterface $getProductQuantityInStock,
        DataObject $stocksInfo,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->stockRepository = $stockRepository;
        $this->salesChannelToStockData = $salesChannelToStockData;
        $this->productRepository = $productRepository;
        $this->getProductQuantityInStock = $getProductQuantityInStock;
        $this->stocksInfo = $stocksInfo;
    }

    /**
     * @param $productSku string
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveStocksQtyInfo($productSku): array
    {
        $salesChannelToStockIds = $this->salesChannelToStockData->execute();
        if ($salesChannelToStockIds) {
            foreach ($salesChannelToStockIds as $stock) {
                $stockId = $stock['stock_id'];
                if ($stockId) {
                    $currentStock = [];
                    $stockData = $this->stockRepository->get($stockId);
                    $currentStock['stock_name'] = $stockData->getName();
                    $currentStock['qty'] = $this->getProductQuantityInStock->execute($productSku, $stockId);
                    $this->stocksInfo->setData($stockId, $currentStock);
                    unset($currentStock);
                }
            }
        }
        return $this->stocksInfo->getData();
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $productSku = $dataSource['data']['product']['sku'];
        $activeStocksInfo = $this->getActiveStocksQtyInfo($productSku);
        if ($activeStocksInfo) {
            foreach ($activeStocksInfo as $stockId => $stockData) {
                $dataSource['data']['stocks'][] = $stockData;
            }
        }
        /*if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['qty'] = $this->getSourceItemsData($row['sku']);
            }
        }
        unset($row);*/

        return $dataSource;
    }
}
