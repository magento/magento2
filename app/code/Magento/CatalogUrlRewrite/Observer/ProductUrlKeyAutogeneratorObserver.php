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
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;

class ProductUrlKeyAutogeneratorObserver implements ObserverInterface
{
    /**
     * @var ProductUrlPathGenerator
     */
    protected $productUrlPathGenerator;

    /**
     * @var CompositeUrlKey
     */
    private $compositeUrlValidator;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param CompositeUrlKey $compositeUrlValidator
     */
    public function __construct(
        ProductUrlPathGenerator $productUrlPathGenerator,
        CompositeUrlKey $compositeUrlValidator
    ) {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->compositeUrlValidator = $compositeUrlValidator;
    }

    /**
     * Validates and sets url key for product
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        $urlKey = $this->productUrlPathGenerator->getUrlKey($product);
        if (null !== $urlKey) {
            $errors = $this->compositeUrlValidator->validate($urlKey);
            if (!empty($errors)) {
                throw new LocalizedException($errors[0]);
            }
            $product->setUrlKey($urlKey);
        }
    }
}
