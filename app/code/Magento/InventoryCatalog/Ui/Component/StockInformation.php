<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Ui\Component\Container;
use Magento\Inventory\Model\ResourceModel\GetAssignedStockIdsBySku;

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
     * @var GetAssignedStockIdsBySku
     */
    private $getAssignedStockIdsBySku;

    /**
     * @param ContextInterface $context
     * @param StockRepositoryInterface $stockRepository
     * @param GetProductQuantityInStockInterface $getProductQuantityInStock
     * @param GetAssignedStockIdsBySku $getAssignedStockIdsBySku
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StockRepositoryInterface $stockRepository,
        GetProductQuantityInStockInterface $getProductQuantityInStock,
        GetAssignedStockIdsBySku $getAssignedStockIdsBySku,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->stockRepository = $stockRepository;
        $this->getProductQuantityInStock = $getProductQuantityInStock;
        $this->getAssignedStockIdsBySku = $getAssignedStockIdsBySku;
    }

    /**
     * @param string $productSku
     * @return array
     */
    private function getActiveStocksQtyInfo(string $productSku): array
    {
        $stockIds = $this->getAssignedStockIdsBySku->execute($productSku);
        $stockInfo = [];
        if (count($stockIds)) {
            foreach ($stockIds as $stockId) {
                $stockId = (int)$stockId;
                $stock = $this->stockRepository->get($stockId);
                $stockInfo[] = [
                    'stock_name' => $stock->getName(),
                    'qty' => $this->getProductQuantityInStock->execute($productSku, $stockId),
                ];
            }
        }
        return $stockInfo;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['product']['sku']) || empty($dataSource['data']['product']['sku'])) {
            return $dataSource;
        }
        $productSku = $dataSource['data']['product']['sku'];
        $stocksData = $this->getActiveStocksQtyInfo($productSku);
        $dataSource['data']['stocks'] = $stocksData;
        return $dataSource;
    }
}
