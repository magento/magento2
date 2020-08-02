<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Downloadable/_files/product_downloadable.php');
Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/active_quote.php');
/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_order_1', 'reserved_order_id');

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
/** @var $product \Magento\Catalog\Model\Product */
$product = $productRepository->get('downloadable-product');

/** @var $linkCollection \Magento\Downloadable\Model\ResourceModel\Link\Collection */
$linkCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Downloadable\Model\Link::class
)->getCollection()->addProductToFilter(
    $product->getId()
)->addTitleToResult(
    $product->getStoreId()
)->addPriceToResult(
    $product->getStore()->getWebsiteId()
);

/** @var $link \Magento\Downloadable\Model\Link */
$link = $linkCollection->getFirstItem();

$requestInfo = new \Magento\Framework\DataObject(['qty' => 1, 'links' => [$link->getId()]]);

/** @var $cart \Magento\Checkout\Model\Cart */
$cart = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Checkout\Model\Cart::class);
$cart->setQuote($quote);
$cart->addProduct($product, $requestInfo);
$cart->save();

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->removeSharedInstance(\Magento\Checkout\Model\Session::class);
