<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\AwareInterface as ProductAwareInterface;

/**
 * Class List Item Block
 * @since 2.1.1
 */
class Block extends AbstractProduct implements ProductAwareInterface
{
    /**
     * @var ProductInterface
     * @since 2.1.1
     */
    private $product;

    /**
     * {@inheritdoc}
     * @since 2.1.1
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.1
     */
    public function getProduct()
    {
        return $this->product;
    }
}
