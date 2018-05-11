<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;

/**
 * Class provides around Plugin on Magento\CatalogInventory\Model\ResourceModel::correctItemsQty
 * to update data in Inventory source item
 */
class UpdateSourceItemAtLegacyQtyCounterPlugin
{
    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResourceConnection $resourceConnection
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceItemsSaveInterface $sourceItemsSave,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $resourceConnection,
        GetSkusByProductIdsInterface $getSkusByProductIds
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceConnection = $resourceConnection;
        $this->getSkusByProductIds = $getSkusByProductIds;
    }

    /**
     * @param QtyCounterInterface $subject
     * @param callable $proceed
     * @param int[] $items
     * @param int $websiteId
     * @param string $operator +/-
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCorrectItemsQty(
        QtyCounterInterface $subject,
        callable $proceed,
        array $items,
        $websiteId,
        $operator
    ) {
        if (empty($items)) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            $proceed($items, $websiteId, $operator);
            $this->updateSourceItemAtLegacyCatalogInventoryQtyCounter($items, $operator);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param int[] $productQuantitiesByProductId
     * @param string $operator
     * @return void
     */
    private function updateSourceItemAtLegacyCatalogInventoryQtyCounter(
        array $productQuantitiesByProductId,
        $operator
    ) {
        $productQuantitiesBySku = $this->getProductQuantitiesBySku($productQuantitiesByProductId);

        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            SourceItemInterface::SKU,
            array_keys($productQuantitiesBySku),
            'in'
        )->addFilter(
            SourceItemInterface::SOURCE_CODE,
            $this->defaultSourceProvider->getCode()
        )->create();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        foreach ($sourceItems as $sourceItem) {
            $sourceItem->setQuantity(
                $sourceItem->getQuantity() + (float)($operator . $productQuantitiesBySku[$sourceItem->getSku()])
            );
        }
        $this->sourceItemsSave->execute($sourceItems);
    }

    /**
     * @param int[] $productQuantitiesByProductId
     * @return array
     */
    private function getProductQuantitiesBySku(array $productQuantitiesByProductId): array
    {
        $productSkus = $this->getSkusByProductIds->execute(array_keys($productQuantitiesByProductId));

        $productQuantitiesBySku = [];
        foreach ($productSkus as $productId => $productSku) {
            $productQuantitiesBySku[$productSku] = $productQuantitiesByProductId[$productId];
        }
        return $productQuantitiesBySku;
    }
}
