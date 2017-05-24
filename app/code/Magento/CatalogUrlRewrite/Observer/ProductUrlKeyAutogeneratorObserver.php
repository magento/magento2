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

class ProductUrlKeyAutogeneratorObserver implements ObserverInterface
{
    /** @var ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     */
    public function __construct(ProductUrlPathGenerator $productUrlPathGenerator)
    {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        $product->setUrlKey($this->productUrlPathGenerator->getUrlKey($product));
    }
}
