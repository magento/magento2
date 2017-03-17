<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

use Magento\Catalog\Block\Product\ImageBlockBuilder;
use Magento\Swatches\Model\ProductSubstitute;
use Magento\Catalog\Model\Product as ModelProduct;

/**
 * Class ProductImage replace original configurable product with first child
 */
class ProductImageBuilder
{
    /**
     * @var ProductSubstitute
     */
    private $productSubstitute;

    /**
     * @param ProductSubstitute|null $productSubstitute
     */
    public function __construct (ProductSubstitute $productSubstitute)
    {
        $this->productSubstitute = $productSubstitute;
    }

    /**
     * Replace original configurable product with first child
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ImageBlockBuilder $subject
     * @param ModelProduct $product
     * @param string $location
     * @return array
     */
    public function beforeBuildBlock(
        ImageBlockBuilder $subject,
        ModelProduct $product,
        $location
    ) {
        return $this->productSubstitute->replace($product, $location);
    }
}
