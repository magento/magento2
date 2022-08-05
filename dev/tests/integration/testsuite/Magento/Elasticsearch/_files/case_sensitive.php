<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_boolean_attribute.php');

/** @var $objectManager \Magento\Framework\ObjectManagerInterface */
$objectManager = Bootstrap::getObjectManager();

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
try {
    $productRepository->get('fulltext-1');
} catch (NoSuchEntityException $e) {
    /** @var $productFirst Product */
    $productFirst = $objectManager->create(Product::class);
    $productFirst->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('A')
        ->setSku('fulltext-1')
        ->setPrice(10)
        ->setMetaTitle('first meta title')
        ->setMetaKeyword('first meta keyword')
        ->setMetaDescription('first meta description')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(1)
        ->save();
}

try {
    $productRepository->get('fulltext-2');
} catch (NoSuchEntityException $e) {
    /** @var $productSecond Product */
    $productSecond = $objectManager->create(Product::class);
    $productSecond->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('B')
        ->setSku('fulltext-2')
        ->setPrice(20)
        ->setMetaTitle('second meta title')
        ->setMetaKeyword('second meta keyword')
        ->setMetaDescription('second meta description')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(1)
        ->save();
}

try {
    $productRepository->get('fulltext-3');
} catch (NoSuchEntityException $e) {
    /** @var $productThird Product */
    $productThird = $objectManager->create(Product::class);
    $productThird->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('C')
        ->setSku('fulltext-3')
        ->setPrice(20)
        ->setMetaTitle('third meta title')
        ->setMetaKeyword('third meta keyword')
        ->setMetaDescription('third meta description')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(1)
        ->save();
}

try {
    $productRepository->get('fulltext-4');
} catch (NoSuchEntityException $e) {
    /** @var $productFourth Product */
    $productFourth = $objectManager->create(Product::class);
    $productFourth->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('a')
        ->setSku('fulltext-4')
        ->setPrice(20)
        ->setMetaTitle('fourth meta title')
        ->setMetaKeyword('fourth meta keyword')
        ->setMetaDescription('fourth meta description')
        ->setUrlKey('aa')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(0)
        ->save();
}

try {
    $productRepository->get('fulltext-5');
} catch (NoSuchEntityException $e) {
    /** @var $productFifth Product */
    $productFifth = $objectManager->create(Product::class);
    $productFifth->setTypeId('simple')
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('b')
        ->setSku('fulltext-5')
        ->setPrice(20)
        ->setMetaTitle('fifth meta title')
        ->setMetaKeyword('fifth meta keyword')
        ->setMetaDescription('fifth meta description')
        ->setUrlKey('bb')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 0])
        ->setBooleanAttribute(0)
        ->save();
}
