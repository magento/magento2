<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Cache\IdentityInterface;

/**
 * Identity for resolved media gallery for resolver cache type
 */
class ResolverCacheIdentity implements IdentityInterface
{
    /**
     * @var string
     */
    public const CACHE_TAG = 'gql_media_gallery';

    /**
     * @inheritDoc
     */
    public function getIdentities($resolvedData, ?array $parentResolvedData = null): array
    {
        if (empty($resolvedData)) {
            return [];
        }
        /** @var Product $mediaGalleryEntryProduct */
        $mediaGalleryEntryProduct = array_pop($resolvedData)['model'];
        return [
            sprintf('%s_%s', self::CACHE_TAG, $mediaGalleryEntryProduct->getId())
        ];
    }
}
