<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin\Product\Gallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\CreateHandler as GalleryCreateHandler;

/**
 * This is a plugin to \Magento\Catalog\Model\Product\Gallery\CreateHandler.
 * It is to set product media gallery changed when such change happens.
 */
class CreateHandler
{
    /**
     * Set product media gallery changed after a product's media gallory is created or updated
     *
     * @param GalleryCreateHandler $subject
     * @param callable $proceed
     * @param Product $product
     * @param array $arguments
     * @return Product
     */
    public function aroundExecute(
        GalleryCreateHandler $subject,
        callable $proceed,
        Product $product,
        array $arguments
    ): Product {
        $result = $proceed($product, $arguments);
        $result->setData('is_media_gallery_changed', true);
        return $result;
    }
}
