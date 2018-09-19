<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Indexer\Plugin;

use Magento\Elasticsearch\Model\Config;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

/**
 * Plugin for filtering child products that are out of stock for preventing their saving to catalog search index.
 */
class StockedProductsFilterPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $stockStatusRepository;

    /**
     * @var StockStatusCriteriaInterfaceFactory
     */
    private $stockStatusCriteriaFactory;

    /**
     * @param Config $config
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     */
    public function __construct(
        Config $config,
        StockConfigurationInterface $stockConfiguration,
        StockStatusRepositoryInterface $stockStatusRepository,
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
    ) {
        $this->config = $config;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->stockStatusCriteriaFactory = $stockStatusCriteriaFactory;
    }

    /**
     * Filter out of stock options for configurable product.
     *
     * @param DataProvider $dataProvider
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePrepareProductIndex(
        DataProvider $dataProvider,
        array $indexData,
        array $productData,
        int $storeId
    ): array {
        if ($this->config->isElasticsearchEnabled() && !$this->stockConfiguration->isShowOutOfStock($storeId)) {
            $productIds = array_keys($indexData);
            $stockStatusCriteria = $this->stockStatusCriteriaFactory->create();
            $stockStatusCriteria->setProductsFilter($productIds);
            $stockStatusCollection = $this->stockStatusRepository->getList($stockStatusCriteria);
            $stockStatuses = $stockStatusCollection->getItems();
            $stockStatuses = array_filter($stockStatuses, function (StockStatusInterface $stockStatus) {
                return StockStatusInterface::STATUS_IN_STOCK == $stockStatus->getStockStatus();
            });
            $indexData = array_intersect_key($indexData, $stockStatuses);
        }

        return [
            $indexData,
            $productData,
            $storeId,
        ];
    }
}
