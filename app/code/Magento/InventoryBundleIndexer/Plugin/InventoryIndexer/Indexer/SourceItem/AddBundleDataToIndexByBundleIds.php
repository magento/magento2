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
use Magento\InventoryBundleIndexer\Indexer\SourceItem\BundleBySkuAndChildrenSourceItemsIdsIndexer;
use Magento\InventoryBundleIndexer\Indexer\SourceItem\GetChildrenSourceItemsIdsByChildrenProductIds;

class AddBundleDataToIndexByBundleIds
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var BundleBySkuAndChildrenSourceItemsIdsIndexer
     */
    private $bundleBySkuAndChildrenSourceItemsIdsIndexer;

    /**
     * @var GetChildrenSourceItemsIdsByChildrenProductIds
     */
    private $getChildrenSourceItemsIdsByChildrenProductIds;

    /**
     * @param ProductRepository $productRepository
     * @param BundleBySkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer
     * @param GetChildrenSourceItemsIdsByChildrenProductIds $getChildrenSourceItemsIdsByChildrenProductIds
     */
    public function __construct(
        ProductRepository $productRepository,
        BundleBySkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer,
        GetChildrenSourceItemsIdsByChildrenProductIds $getChildrenSourceItemsIdsByChildrenProductIds
    ) {
        $this->productRepository = $productRepository;
        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer = $bundleBySkuAndChildrenSourceItemsIdsIndexer;
        $this->getChildrenSourceItemsIdsByChildrenProductIds = $getChildrenSourceItemsIdsByChildrenProductIds;
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
                $product->getSku() => $this->getChildrenSourceItemsIdsByChildrenProductIds->execute($childrenIds)
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
