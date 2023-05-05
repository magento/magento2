<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogGraphQl\Plugin\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery\ResolverCacheIdentity;

/**
 * This is a plugin to \Magento\Catalog\Model\Product.
 * It is to add media gallery identities to product identities.
 */
class UpdateIdentities
{
    /**
     * Set product media gallery changed after add image to the product
     *
     * @param Product $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(Product $subject, array $result): array
    {
        if ($subject->getData('is_media_gallery_changed')) {
            $result[] = sprintf('%s_%s', ResolverCacheIdentity::CACHE_TAG, $subject->getData('row_id'));
        }
        return $result;
    }
}
