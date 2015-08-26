<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Model\Product;

/**
 * Class AbstractMediaGalleryEntryProcessor
 */
abstract class AbstractMediaGalleryEntryProcessor
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media
     */
    private $resourceEntryMediaGallery;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $resourceEntryMediaGallery
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $resourceEntryMediaGallery
    ) {
        $this->resourceEntryMediaGallery = $resourceEntryMediaGallery;
    }

    /**
     * @param Product $product
     * @return void
     */
    abstract public function afterLoad(Product $product, $attributeCode);

    /**
     * @param Product $product
     * @return void
     */
    abstract public function beforeSave(Product $product, $attributeCode);

    /**
     * @param Product $product
     * @return void
     */
    abstract public function afterSave(Product $product, $attributeCode);

    /**
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media
     */
    protected function getResourceEntryMediaGallery()
    {
        return $this->resourceEntryMediaGallery;
    }
}
