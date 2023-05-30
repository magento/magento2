<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin\Product\Gallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\CopyHandler as GalleryCopyHandler;

/**
 * This is a plugin to \Magento\Catalog\Model\Product\Gallery\CopyHandler.
 * It is to set product media gallery changed when such change happens.
 */
class CopyHandler
{
    /**
     * Set product media gallery changed after media gallory copied to the product
     *
     * @param GalleryCopyHandler $subject
     * @param callable $proceed
     * @param Product $product
     * @param array $arguments
     * @return void
     */
    public function aroundExecute(
        GalleryCopyHandler $subject,
        callable $proceed,
        Product $product,
        array $arguments
    ): void {
        $proceed($product, $arguments);
        $product->setData('is_media_gallery_changed', true);
    }
}
