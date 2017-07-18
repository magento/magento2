<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Checkout/_files/quote_with_address.php';
require __DIR__ . '/../../Catalog/_files/product_simple.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product = $productRepository->get('simple');

$options = [];
/** @var $option \Magento\Catalog\Model\Product\Option */
foreach ($product->getOptions() as $option) {
    switch ($option->getGroupByType()) {
        case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_GROUP_DATE:
            $value = ['year' => 2013, 'month' => 8, 'day' => 9, 'hour' => 13, 'minute' => 35];
            break;
        case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_GROUP_SELECT:
            $value = key($option->getValues());
            break;
        default:
            $value = 'test';
            break;
    }
    $options[$option->getId()] = $value;
}

$requestInfo = new \Magento\Framework\DataObject(['qty' => 1, 'options' => $options]);

$quote->setReservedOrderId('test_order_item_with_items_and_custom_options')
    ->addProduct($product, $requestInfo);
$quote->collectTotals();
$objectManager->get(\Magento\Quote\Model\QuoteRepository::class)->save($quote);

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
