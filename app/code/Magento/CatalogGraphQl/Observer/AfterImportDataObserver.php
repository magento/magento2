<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Observer;

use Magento\Framework\Event\Observer;
use Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery\ResolverCacheIdentity;
use Magento\Framework\Event\ObserverInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;

class AfterImportDataObserver implements ObserverInterface
{
    /**
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

    /**
     * @param GraphQlResolverCache $graphQlResolverCache
     */
    public function __construct(GraphQlResolverCache $graphQlResolverCache)
    {
        $this->graphQlResolverCache = $graphQlResolverCache;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $mediaGalleryEntriesChanged = $observer->getEvent()->getMediaGallery();

        $productSkusToInvalidate = [];

        foreach ($mediaGalleryEntriesChanged as $productSkus) {
            $productSkusToInvalidate[] = array_keys($productSkus);
        }

        $productSkusToInvalidate = array_merge([], ...$productSkusToInvalidate);

        $tags = array_map(function ($productSku) {
            return sprintf('%s_%s', ResolverCacheIdentity::CACHE_TAG, $productSku);
        }, $productSkusToInvalidate);

        if (!empty($tags)) {
            $this->graphQlResolverCache->clean(
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                $tags
            );
        }
    }
}
