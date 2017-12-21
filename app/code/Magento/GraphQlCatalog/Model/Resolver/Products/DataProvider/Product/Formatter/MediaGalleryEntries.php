<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;

/**
 * Format a product's media gallery information to conform to GraphQL schema representation
 */
class MediaGalleryEntries
{
    /**
     * Format product's media gallery entry data to conform to GraphQL schema
     *
     * @param Product $product
     * @param array $productData
     * @return array
     */
    public function format(Product $product, array $productData)
    {
        $productData['media_gallery_entries'] = $product->getMediaGalleryEntries();
        if (isset($productData['media_gallery_entries'])) {
            foreach ($productData['media_gallery_entries'] as $key => $entry) {
                if ($entry->getExtensionAttributes() && $entry->getExtensionAttributes()->getVideoContent()) {
                    $productData['media_gallery_entries'][$key]['video_content']
                        = $entry->getExtensionAttributes()->getVideoContent()->getData();
                }
            }
        }

        return $productData;
    }
}
