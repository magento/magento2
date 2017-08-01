<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\CatalogUrlRewrite\Observer\ProductUrlKeyAutogeneratorObserver
 *
 * @since 2.0.0
 */
class ProductUrlKeyAutogeneratorObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     * @since 2.0.0
     */
    protected $productUrlPathGenerator;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @since 2.0.0
     */
    public function __construct(ProductUrlPathGenerator $productUrlPathGenerator)
    {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        $product->setUrlKey($this->productUrlPathGenerator->getUrlKey($product));
    }
}
