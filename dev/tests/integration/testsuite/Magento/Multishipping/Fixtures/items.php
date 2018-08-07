<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Quote\Model\Quote\Item;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * @var Magento\Quote\Model\Quote $quote
 */

if (empty($quote)) {
    throw new \Exception('$quote should be defined in the parent fixture');
}

$productList = [];
require __DIR__ . '/simple_product_10.php';
$productList[] = $product;

require __DIR__ . '/simple_product_20.php';
$productList[] = $product;

require __DIR__ . '/virtual_product_5.php';
$productList[] = $product;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

foreach ($productList as $product) {
    /** @var Item $item */
    $item = $objectManager->create(Item::class);
    $item->setProduct($product)
        ->setPrice($product->getPrice())
        ->setQty(1);
    $quote->addItem($item);
}
