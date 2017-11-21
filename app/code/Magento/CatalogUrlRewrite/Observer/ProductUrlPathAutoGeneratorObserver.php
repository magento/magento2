<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductUrlPathAutoGeneratorObserver implements ObserverInterface
{
    /** @var ProductUrlPathGenerator */
    private $productUrlPathGenerator;

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
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        $product->setUrlKey($this->productUrlPathGenerator->getUrlPath($product));
    }
}
