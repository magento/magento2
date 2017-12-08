<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class provides around Plugin on Magento\CatalogInventory\Model\ResourceModel::correctItemsQty
 * to update data in Inventory source item
 */
class UpdateSourceItemAtLegacyCatalogInventoryQtyCounter
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
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceItemsSaveInterface $sourceItemsSave,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->productRepository = $productRepository;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param int[] $items
     * @param int $websiteId
     * @param string $operator +/-
     *
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCorrectItemsQty($subject, callable $proceed, array $items, $websiteId, $operator)
    {
        $proceed($items, $websiteId, $operator);

        if ($websiteId !== 0) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            $productsData = $this->getProductData($items);

            $searchCriteria =
                $this->searchCriteriaBuilder->addFilter(SourceItemInterface::SKU, array_keys($productsData), 'in')
                                            ->addFilter(SourceItemInterface::SOURCE_ID, 1)
                                            ->create();

            $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
            $sourceItems = array_map(
                function (SourceItemInterface $item) use ($productsData, $operator) {
                    $item->setQuantity($item->getQuantity() + (int)($operator.$productsData[$item->getSku()]));

                    return $item;
                },
                $sourceItems
            );

            $this->sourceItemsSave->execute($sourceItems);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param int[] $items
     *
     * @return array
     */
    private function getProductData(array $items)
    {
        $productData = [];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', array_keys($items), 'in')->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();
        foreach ($products as $product) {
            $productData[$product->getSku()] = $items[$product->getId()];
        }

        return $productData;
    }
}
