<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Product\Attribute\Media;

use Magento\Catalog\Model\Product\Attribute\Backend\Media\AbstractMediaGalleryEntryProcessor;
use Magento\Catalog\Model\Product;

/**
 * Class ImageMediaGalleryEntryProcessor
 */
class ExternalVideoMediaGalleryEntryProcessor extends AbstractMediaGalleryEntryProcessor
{
    protected $videoProperties = [
        'video_value_id',
        'video_provider',
        'video_url',
        'video_title',
        'video_description',
        'video_metadata'
    ];

    /**
     * @param Product $product
     * @return void
     */
    public function afterLoad(Product $product, $attributeCode)
    {

    }

    /**
     * @param Product $product
     * @return void
     */
    public function beforeSave(Product $product, $attributeCode)
    {

    }

    /**
     * @param Product $product
     * @return void
     */
    public function afterSave(Product $product, $attributeCode)
    {

    }
}
