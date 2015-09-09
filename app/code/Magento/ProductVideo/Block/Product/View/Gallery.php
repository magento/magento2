<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Simple product data view
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ProductVideo\Block\Product\View;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * Retrieve media gallery data in JSON format
     *
     * @return string
     */
    public function getMediaGalleryDataJson()
    {
        $mediaGalleryData = [];
        foreach ($this->getProduct()->getMediaGalleryImages() as $mediaGalleryImage) {
            $mediaGalleryData[] = [
                'mediaType' => $mediaGalleryImage->getMediaType(),
                'videoUrl' => $mediaGalleryImage->getVideoUrl(),
                'isBase' => $this->isMainImage($mediaGalleryImage),
            ];
        }
        return json_encode($mediaGalleryData);
    }
}
