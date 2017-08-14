<?php
/**
 * Copyright © Magento, Inc. All rights reserved. 
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;

/**
 * Collect additional information about product, in order to allow product rendering on front
 */
class AdditionalInfo implements ProductRenderCollectorInterface
{
    /**
     * @inheritdoc
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $productRender->setIsSalable($product->isSalable());
        $productRender->setType($product->getTypeId());
        $productRender->setName($product->getName());
        $productRender->setId($product->getId());
    }
}
