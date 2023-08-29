<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Observer;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery\ResolverCacheIdentity;
use Magento\Framework\Event\ObserverInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;

/**
 * Clean media gallery resolver cache for product SKUs after importing data to database
 */
class AfterImportDataObserver implements ObserverInterface
{
    /**
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     * @param ProductRepository $productRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     */
    public function __construct(
        GraphQlResolverCache $graphQlResolverCache,
        ProductRepository $productRepository,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->graphQlResolverCache = $graphQlResolverCache;
        $this->productRepository = $productRepository;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $mediaGalleryEntriesChanged = (array) $observer->getEvent()->getMediaGallery();
        $mediaGalleryLabelsChanged = (array) $observer->getEvent()->getMediaGalleryLabels();
        $productIdsToDelete = (array) $observer->getEvent()->getIdsToDelete();

        if (empty($mediaGalleryEntriesChanged) &&
            empty($mediaGalleryLabelsChanged) &&
            empty($productIdsToDelete)
        ) {
            return;
        }

        $productSkusToInvalidate = [];

        foreach ($mediaGalleryEntriesChanged as $productSkus) {
            $productSkusToInvalidate[] = array_keys($productSkus);
        }

        foreach ($mediaGalleryLabelsChanged as $label) {
            $productSkusToInvalidate[] = [$label['imageData']['sku']];
        }

        $productSkusToInvalidate = array_unique(array_merge(...$productSkusToInvalidate));
        $products = $this->productRepository->getList(
            $this->criteriaBuilder->addFilter('sku', $productSkusToInvalidate, 'in')->create()
        )->getItems();

        $productIds = array_map(function ($product) {
            return $product->getId();
        }, $products);

        $productIdsToInvalidate = array_unique(array_merge($productIds, $productIdsToDelete));

        $tags = array_map(function ($productId) {
            return sprintf('%s_%s', ResolverCacheIdentity::CACHE_TAG, $productId);
        }, $productIdsToInvalidate);

        $this->graphQlResolverCache->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            $tags
        );
    }
}
