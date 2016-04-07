<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/product_simple.php';
require __DIR__ . '/../../../Magento/Checkout/_files/active_quote.php';

$optionValue = [
    'field' => 'Test value',
    'date_time' => [
        'year' => '2015',
        'month' => '9',
        'day' => '9',
        'hour' => '2',
        'minute' => '2',
        'day_part' => 'am',
        'date_internal' => '',
    ],
    'drop_down' => '3-1-select',
    'radio' => '4-1-radio',
];

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
/** @var \Magento\Catalog\Api\Data\ProductInterface $product */
$product = $productRepository->get('simple');

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create('Magento\Quote\Model\Quote');
/** @var \Magento\Quote\Api\CartItemRepositoryInterface  $quoteItemRepository */
$quoteItemRepository = $objectManager->create('\Magento\Quote\Api\CartItemRepositoryInterface');
/** @var \Magento\Quote\Api\Data\CartItemInterface $cartItem */
$cartItem = $objectManager->create('Magento\Quote\Api\Data\CartItemInterface');
/** @var \Magento\Quote\Model\Quote\ProductOption $productOption */
$productOption = $objectManager->create('Magento\Quote\Model\Quote\ProductOptionFactory')->create();
/** @var  \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensionAttributes */
$extensionAttributes = $objectManager->create('Magento\Quote\Api\Data\ProductOptionExtensionFactory')->create();
$customOptionFactory = $objectManager->create('Magento\Catalog\Model\CustomOptions\CustomOptionFactory');
$options = [];
/** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option */
foreach ($product->getOptions() as $option) {
    /** @var \Magento\Catalog\Api\Data\CustomOptionInterface $customOption */
    $customOption = $customOptionFactory->create();
    $customOption->setOptionId($option->getId());
    $customOption->setOptionValue($optionValue[$option->getType()]);
    $options[] = $customOption;
}


$quote->load('test_order_1', 'reserved_order_id');
$cartItem->setQty(1);
$cartItem->setSku('simple');
$cartItem->setQuoteId($quote->getId());

$extensionAttributes->setCustomOptions($options);
$productOption->setExtensionAttributes($extensionAttributes);
$cartItem->setProductOption($productOption);

$quoteItemRepository->save($cartItem);
