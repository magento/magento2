<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Block\Product;

use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\Image;

/**
 * Provides product image to be used in the Product Alert Email.
 */
class ImageProvider
{
    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    /**
     * @param ImageBuilder $imageBuilder
     */
    public function __construct(
        ImageBuilder $imageBuilder
    ) {
        $this->imageBuilder = $imageBuilder;
    }

    /**
     * Gets Product Image Block
     *
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     * @return Image
     * @throws \Exception
     */
    public function getImage(Product $product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }
}
