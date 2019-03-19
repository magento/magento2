<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/CatalogInventory/_files/simple_product_decimal_qty.php';

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->create(ProductRepositoryInterface::class);
/** @var $product \Magento\Catalog\Model\Product */
$product = $productRepository->get('simple_with_decimal_qty');

/** @var Quote $quote */
$quote = Bootstrap::getObjectManager()->create(Quote::class);
$quote->setReservedOrderId('decimal_quote_id');
$item = $objectManager->create(\Magento\Quote\Model\Quote\Item::class);
$item->setProduct($product)
    ->setPrice($product->getPrice())
    ->setQty(1.1);
$quote->addItem($item);


/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);
