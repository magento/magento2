<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;

/**
 * Class provides around Plugin on Magento\CatalogInventory\Model\ResourceModel::correctItemsQty
 * to update data in Inventory source item
 */
class UpdateSourceItemAtLegacyCatalogInventoryQtyCounterPlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
     * @param ProductRepositoryInterface $productRepository
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceItemsSaveInterface $sourceItemsSave,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->productRepository = $productRepository;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceConnection = $resourceConnection;
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
            SourceItemInterface::SOURCE_ID,
            $this->defaultSourceProvider->getId()
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
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', array_keys($productQuantitiesByProductId), 'in')
            ->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        $productQuantitiesBySku = [];
        foreach ($products as $product) {
            $productQuantitiesBySku[$product->getSku()] = $productQuantitiesByProductId[$product->getId()];
        }
        return $productQuantitiesBySku;
    }
}
