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
use Magento\InventoryBundleIndexer\Indexer\SourceItem\ByBundleSkuAndChildrenSourceItemsIdsIndexer;
use Magento\InventoryBundleIndexer\Indexer\SourceItem\SourceItemsIdsByChildrenProductsIdsProvider;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;

class AddBundleDataToIndexByBundleIds
{
    /**
     * @var ByBundleSkuAndChildrenSourceItemsIdsIndexer
     */
    private $bundleBySkuAndChildrenSourceItemsIdsIndexer;

    /**
     * @var SourceItemsIdsByChildrenProductsIdsProvider
     */
    private $sourceItemsIdsByChildrenProductsIdsProvider;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param ByBundleSkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer
     * @param SourceItemsIdsByChildrenProductsIdsProvider $sourceItemsIdsByChildrenProductsIdsProvider
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     */
    public function __construct(
        ByBundleSkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer,
        SourceItemsIdsByChildrenProductsIdsProvider $sourceItemsIdsByChildrenProductsIdsProvider,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer = $bundleBySkuAndChildrenSourceItemsIdsIndexer;
        $this->sourceItemsIdsByChildrenProductsIdsProvider = $sourceItemsIdsByChildrenProductsIdsProvider;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
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
                $sku = $productLink->getSku();
                $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];
            }
            $productIds[] = $productId;
        }

        return $productIds;
    }
}
