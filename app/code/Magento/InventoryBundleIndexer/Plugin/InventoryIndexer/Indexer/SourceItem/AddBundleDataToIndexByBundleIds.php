<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Plugin\InventoryIndexer\Indexer\SourceItem;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\InventoryBundleIndexer\Indexer\SourceItem\ByBundleSkuAndChildrenSourceItemsIdsIndexer;
use Magento\InventoryBundleIndexer\Indexer\SourceItem\SourceItemsIdsByChildrenProductsIdsProvider;

class AddBundleDataToIndexByBundleIds
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ByBundleSkuAndChildrenSourceItemsIdsIndexer
     */
    private $bundleBySkuAndChildrenSourceItemsIdsIndexer;

    /**
     * @var SourceItemsIdsByChildrenProductsIdsProvider
     */
    private $sourceItemsIdsByChildrenProductsIdsProvider;

    /**
     * @param ProductRepository $productRepository
     * @param ByBundleSkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer
     * @param SourceItemsIdsByChildrenProductsIdsProvider $sourceItemsIdsByChildrenProductsIdsProvider
     */
    public function __construct(
        ProductRepository $productRepository,
        ByBundleSkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer,
        SourceItemsIdsByChildrenProductsIdsProvider $sourceItemsIdsByChildrenProductsIdsProvider
    ) {
        $this->productRepository = $productRepository;
        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer = $bundleBySkuAndChildrenSourceItemsIdsIndexer;
        $this->sourceItemsIdsByChildrenProductsIdsProvider = $sourceItemsIdsByChildrenProductsIdsProvider;
    }

    /**
     * @param ProductOptionRepositoryInterface $subject
     * @param $result
     * @param ProductInterface $product
     * @param OptionInterface $option
     *
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        ProductOptionRepositoryInterface $subject,
        $result,
        ProductInterface $product,
        OptionInterface $option
    ) {
        $childrenIds = $this->getChildrenProductIdsByProductLinks($option->getProductLinks());

        $bundleChildrenSourceItemsIdsWithSku = [
                $product->getSku() => $this->sourceItemsIdsByChildrenProductsIdsProvider->execute($childrenIds)
            ];

        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer->execute($bundleChildrenSourceItemsIdsWithSku);

        return $result;
    }

    /**
     * @param array $productLinks
     *
     * @return array
     */
    private function getChildrenProductIdsByProductLinks(array $productLinks): array
    {
        $productIds = [];
        foreach ($productLinks as $productLink) {
            $productId = $productLink->getProductId();
            if (null === $productId) {
                $productId = $this->productRepository->get($productLink->getSku())->getId();
            }
            $productIds[] = $productId;
        }

        return $productIds;
    }
}
