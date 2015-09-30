<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/product_simple.php';
require __DIR__ . '/../../../Magento/Checkout/_files/quote_with_address_saved.php';

function getOptionValue(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option)
{
    $returnValue = null;
        switch ($option->getType()) {
            case 'field' :
                $returnValue = 'Test value';
                break;
            case 'date_time' :
                $returnValue = [
                    'year' => '2015',
                    'month' => '9',
                    'day' => '9',
                    'hour' => '2',
                    'minute' => '2',
                    'day_part' => 'am',
                    'date_internal' => '',
                ];
                break;
            case 'drop_down' :
                $returnValue = '3-1-select';
                break;
            case 'radio' :
                $returnValue = '4-1-radio';
                break;
    }
    return $returnValue;
}


$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
/** @var \Magento\Catalog\Api\Data\ProductInterface $product */
$product = $productRepository->get('simple');

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create('Magento\Quote\Model\Quote');
/** @var \Magento\Quote\Model\Quote\Item\Repository  $quoteItemRepository */
$quoteItemRepository = $objectManager->create('Magento\Quote\Model\Quote\Item\Repository');
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
    $customOption->setOptionValue(getOptionValue($option));
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
