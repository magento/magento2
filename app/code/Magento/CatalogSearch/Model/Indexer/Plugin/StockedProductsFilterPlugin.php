<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Plugin;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

/**
 * Plugin for filtering child products that are out of stock for preventing their saving to catalog search index.
 *
 * This plugin reverts changes introduced in commit 9ab466d8569ea556cb01393989579c3aac53d9a3 which break extensions
 * relying on stocks. Plugin location is changed for consistency purposes.
 */
class StockedProductsFilterPlugin
{
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
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockStatusRepositoryInterface $stockStatusRepository,
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
    ) {
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
        if (!$this->stockConfiguration->isShowOutOfStock($storeId)) {
            $productIds = array_keys($indexData);
            $stockStatusCriteria = $this->stockStatusCriteriaFactory->create();
            $stockStatusCriteria->setProductsFilter($productIds);
            $stockStatusCollection = $this->stockStatusRepository->getList($stockStatusCriteria);
            $stockStatuses = $stockStatusCollection->getItems();
            $stockStatuses = array_filter(
                $stockStatuses,
                function (StockStatusInterface $stockStatus) {
                    return StockStatusInterface::STATUS_IN_STOCK == $stockStatus->getStockStatus();
                }
            );
            $indexData = array_intersect_key($indexData, $stockStatuses);
        }

        return [
            $indexData,
            $productData,
            $storeId,
        ];
    }
}
