<?php

namespace Magento\InventoryCatalog\Block\Adminhtml\Product\Edit\Tab;

use Magento\Framework\DataObject;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\GetSalesChannelToStockDataInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Template;

class StockInformation extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_InventoryCatalog::stocks.phtml';

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

    /**
     * @param Context $context
     * @param StockRepositoryInterface $stockRepository
     * @param GetSalesChannelToStockDataInterface $salesChannelToStockData
     * @param ProductRepositoryInterface $productRepository
     * @param GetProductQuantityInStockInterface $getProductQuantityInStock
     * @param DataObject $stocksInfo
     * @param array $data
     */
    public function __construct(
        Context $context,
        StockRepositoryInterface $stockRepository,
        GetSalesChannelToStockDataInterface $salesChannelToStockData,
        ProductRepositoryInterface $productRepository,
        GetProductQuantityInStockInterface $getProductQuantityInStock,
        DataObject $stocksInfo,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->stockRepository = $stockRepository;
        $this->salesChannelToStockData = $salesChannelToStockData;
        $this->productRepository = $productRepository;
        $this->getProductQuantityInStock = $getProductQuantityInStock;
        $this->stocksInfo = $stocksInfo;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductSku(): string
    {
        $productId = $this->getRequest()->getParam('id');
        $_product = $this->productRepository->getById($productId);
        return $_product->getSku();
    }

    /**
     * @return DataObject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveStocksQtyInfo(): DataObject
    {
        $productSku = $this->getProductSku();
        $salesChannelToStockIds = $this->salesChannelToStockData->execute();
        if ($salesChannelToStockIds) {
            foreach ($salesChannelToStockIds as $stock) {
                $stockId = $stock['stock_id'];
                if ($stockId) {
                    $currentStock = [];
                    $stockData = $this->stockRepository->get($stockId);
                    $currentStock['name'] = $stockData->getName();
                    $currentStock['qty'] = $this->getProductQuantityInStock->execute($productSku, $stockId);
                    $this->stocksInfo->setData($stockId, $currentStock);
                    unset($currentStock);
                }
            }
        }
        return $this->stocksInfo;
    }
}
