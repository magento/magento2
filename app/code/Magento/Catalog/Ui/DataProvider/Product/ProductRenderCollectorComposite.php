<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;

/**
 * Composite, which holds collectors, that collect enought information for
 * product render
 * @since 2.2.0
 */
class ProductRenderCollectorComposite implements ProductRenderCollectorInterface
{
    /**
     * @var ProductRenderCollectorInterface[]
     * @since 2.2.0
     */
    private $productProviders = [];

    /**
     * ProductRenderCollectorComposite constructor.
     * @param array $productProviders
     * @since 2.2.0
     */
    public function __construct(array $productProviders = [])
    {
        $this->productProviders = $productProviders;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        foreach ($this->productProviders as $provider) {
            $provider->collect($product, $productRender);
        }
    }
}
