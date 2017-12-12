<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel\Stock;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ResourceItem;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Model\AbstractModel;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;

/**
 * Class provides around Plugin on Magento\CatalogInventory\Model\ResourceModel\Stock\Item::delete
 * to update data in Inventory source item
 */
class DeleteSourceItemsAtLegacyStockItemDelete
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemsDeleteInterface
     */
    private $sourceItemsDelete;
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resourceConnection
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceItemsDeleteInterface $sourceItemsDelete
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resourceConnection,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceItemsDeleteInterface $sourceItemsDelete,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->productRepository = $productRepository;
        $this->resourceConnection = $resourceConnection;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceItemsDelete = $sourceItemsDelete;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * @param ResourceItem $subject
     * @param callable $proceed
     * @param AbstractModel $stockItem
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundDelete(ResourceItem $subject, callable $proceed, AbstractModel $stockItem)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            $proceed($stockItem);

            $product = $this->productRepository->getById($stockItem->getProductId());
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SourceItemInterface::SKU, $product->getSku())
                ->addFilter(SourceItemInterface::SOURCE_ID, $this->defaultSourceProvider->getId())
                ->create();
            $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

            $this->sourceItemsDelete->execute($sourceItems);
            $connection->commit();
        } catch (CouldNotSaveException $e) {
            $connection->rollBack();
        } catch (InputException $e) {
            $connection->rollBack();
        }
    }
}
