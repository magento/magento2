<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Component\Listing\Column;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
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
     * @var array
     */
    private $stocks;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

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
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->productQuantityInStock = $productQuantityInStock;
        $this->stockRepository = $stockRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        $stockData = [];
        foreach ($this->getStocks() as $stockName => $stockId) {

            try {
                $qty = $this->productQuantityInStock->execute($sku, $stockId);
            } catch (\Exception $exception) {
                $qty = '-';
            }

            $stockData[] = [
                'stock_name' => $stockName,
                'qty' => $qty
            ];
        }

        return $stockData;
    }

    /**
     * Return all stocks as list.
     *
     * @return array [ stockName => stockId]
     */
    private function getStocks(): array
    {

        if ($this->stocks === null) {
            $this->stocks = [];

            $searchCriteria = $this->searchCriteriaBuilder->create();
            $stocks = $this->stockRepository->getList($searchCriteria)->getItems();

            foreach ($stocks as $stock) {
                $this->stocks[$stock->getName()] = (int) $stock->getStockId();
            }
        }

        return $this->stocks;
    }
}
