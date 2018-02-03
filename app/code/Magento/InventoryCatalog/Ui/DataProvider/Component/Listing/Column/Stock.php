<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Inventory\Model\ResourceModel\GetAssignedStockIdsBySku;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Add grid column with source items data
 */
class Stock extends Column
{
    /**
     * @var GetProductQuantityInStockInterface
     */
    private $productQuantityInStock;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var GetAssignedStockIdsBySku
     */
    private $getAssignedStockIdsBySku;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param GetProductQuantityInStockInterface $getStocksQtyBySku
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        GetProductQuantityInStockInterface $productQuantityInStock,
        StockRepositoryInterface $stockRepository,
        GetAssignedStockIdsBySku $getAssignedStockIdsBySku,
        array $components = [],
        array $data = []
    ) {
        $this->productQuantityInStock = $productQuantityInStock;
        $this->stockRepository = $stockRepository;
        $this->getAssignedStockIdsBySku = $getAssignedStockIdsBySku;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['qty_stock'] = $this->getStockData($row['sku']);
            }
        }
        unset($row);

        return $dataSource;
    }

    /**
     * Prepare the stock data.
     *
     * @param string $sku
     * @return array
     */
    private function getStockData(string $sku): array
    {
        $stockIds = $this->getAssignedStockIdsBySku->execute($sku);
        $stockInfo = [];
        if (count($stockIds)) {
            foreach ($stockIds as $stockId) {
                $stockId = (int)$stockId;
                $stock = $this->stockRepository->get($stockId);
                $stockInfo[] = [
                    'stock_name' => $stock->getName(),
                    'qty' => $this->productQuantityInStock->execute($sku, $stockId),
                ];
            }
        }
        return $stockInfo;
    }
}
