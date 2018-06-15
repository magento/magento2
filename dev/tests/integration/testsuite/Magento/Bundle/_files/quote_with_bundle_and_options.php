<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . 'product_with_multiple_options.php';

$objectManager = Bootstrap::getObjectManager();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->load(3);

/** @var $typeInstance \Magento\Bundle\Model\Product\Type */
$typeInstance = $product->getTypeInstance();
$typeInstance->setStoreFilter($product->getStoreId(), $product);
$optionCollection = $typeInstance->getOptionsCollection($product);

$bundleOptions = [];
$bundleOptionsQty = [];
foreach ($optionCollection as $option) {
    /** @var $option \Magento\Bundle\Model\Option */
    $selectionsCollection = $typeInstance->getSelectionsCollection([$option->getId()], $product);
    if ($option->isMultiSelection()) {
        $bundleOptions[$option->getId()] = array_column($selectionsCollection->toArray(), 'selection_id');
    } else {
        $bundleOptions[$option->getId()] = $selectionsCollection->getFirstItem()->getSelectionId();
    }
    $bundleOptionsQty[$option->getId()] = 1;
}

$requestInfo = new \Magento\Framework\DataObject(
    [
        'product' => $product->getId(),
        'bundle_option' => $bundleOptions,
        'bundle_option_qty' => $bundleOptionsQty,
        'qty' => 1,
    ]
);

/** @var $cart \Magento\Checkout\Model\Cart */
$cart = Bootstrap::getObjectManager()->create(\Magento\Checkout\Model\Cart::class);
$cart->addProduct($product, $requestInfo);
$cart->getQuote()->setReservedOrderId('test_cart_with_bundle_and_options');
$cart->save();

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = Bootstrap::getObjectManager();
$objectManager->removeSharedInstance(\Magento\Checkout\Model\Session::class);
