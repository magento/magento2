<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/**
 * @var Product $productModel
 * @var ProductRepositoryInterface $productRepository
 */
$productModel = $objectManager->create(Product::class);
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

$productModel->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setName('AdvancedPricingSimple 1')
    ->setSku('AdvancedPricingSimple 1')
    ->setPrice(321)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->setIsObjectNew(true);

$productRepository->save($productModel);

$productModel = $objectManager->create(Product::class);
$productModel->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setName('AdvancedPricingSimple 2')
    ->setSku('AdvancedPricingSimple 2')
    ->setPrice(654)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->setIsObjectNew(true);
$productRepository->save($productModel);
