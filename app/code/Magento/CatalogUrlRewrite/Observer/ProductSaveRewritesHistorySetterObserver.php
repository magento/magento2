<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;

/**
 * Class ProductSaveRewritesHistorySetterObserver
 *
 * @package Magento\CatalogUrlRewrite\Observer
 */
class ProductSaveRewritesHistorySetterObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     */
    public function __construct(ProductUrlPathGenerator $productUrlPathGenerator)
    {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
    }

    /**
     * Always set save_rewrites_history to 1 for API calls so there will always be created a SEO rewrite for the old url
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var Product $product
         */
        $product = $observer->getEvent()->getProduct();
        $urlKey = $this->productUrlPathGenerator->getUrlKey($product);
        if (null !== $urlKey) {
            $product->setSaveRewritesHistory(1);
        }
    }
}
