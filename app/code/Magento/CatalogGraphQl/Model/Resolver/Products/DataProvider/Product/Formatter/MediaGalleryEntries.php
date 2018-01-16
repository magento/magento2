<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Format a product's media gallery information to conform to GraphQL schema representation
 */
class MediaGalleryEntries implements FormatterInterface
{
    /**
     * Format product's media gallery entry data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        if (!empty($product->getMediaGalleryEntries())) {
            foreach ($product->getMediaGalleryEntries() as $key => $entry) {
                $productData['media_gallery_entries'][$key] = $entry->getData();
                if ($entry->getExtensionAttributes() && $entry->getExtensionAttributes()->getVideoContent()) {
                    $productData['media_gallery_entries'][$key]['video_content']
                        = $entry->getExtensionAttributes()->getVideoContent()->getData();
                }
            }
        }

        return $productData;
    }
}
