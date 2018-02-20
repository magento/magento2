<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


include __DIR__ . '/product_simple_with_full_option_set.php';

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$product = $productRepository->get('simple', true);
$eavAttributeValues = [
    'category_ids' => [2],
    'cost' => 2.234,
    'country_of_manufacture' => 'US',
    'msrp' => 10.48,
    'gift_message_available' => 0,
    'msrp_display_actual_price_type' => 0,
    'news_from_date' => '2017-08-10',
    'news_to_date' => '2017-08-11',
    'old_id' => 35235,
    'options_container' => 'Options Container',
    'required_options' => 1,
    'special_price' => 3.82,
    'special_from_date' => date('Y-m-d', strtotime('-1 day')),
    'special_to_date' => date('Y-m-d', strtotime('+1 day')),
    'manufacturer' => 'Magento Inc.',
];

foreach ($eavAttributeValues as $attributeCode => $attributeValue) {
    $product->setCustomAttribute($attributeCode, $attributeValue);
}

$productRepository->save($product);
