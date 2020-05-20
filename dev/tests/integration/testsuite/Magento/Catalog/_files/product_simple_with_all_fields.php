<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_with_full_option_set.php');

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(10)
    ->setName('Movable Position 2')
    ->setParentId(2)
    ->setPath('1/2/10')
    ->setLevel(2)
    ->setAvailableSortBy(['name', 'price'])
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(6)
    ->save();

$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(1151)
    ->setName('Filter category')
    ->setParentId(10)
    ->setPath('1/2/10/1151')
    ->setLevel(3)
    ->setAvailableSortBy(['name', 'price'])
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(0)
    ->save();

$product = $productRepository->get('simple', true);
$eavAttributeValues = [
    'category_ids' => [10],
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

/** @var Magento\Catalog\Api\CategoryLinkManagementInterface $linkManagement */
$categoryLinkManagement = $objectManager->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2, 10, 1151]
);
