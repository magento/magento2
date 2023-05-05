<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved CMS page for resolver cache type
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
    public function getIdentities(array $resolvedData): array
    {
        if (empty($resolvedData)) {
            return [];
        }
        /** @var Product $mediaGalleryEntryProduct */
        $mediaGalleryEntryProduct = array_pop($resolvedData)['model'];
        return [
            self::CACHE_TAG,
            sprintf('%s_%s', self::CACHE_TAG, $mediaGalleryEntryProduct->getData('row_id'))
        ];
    }
}
