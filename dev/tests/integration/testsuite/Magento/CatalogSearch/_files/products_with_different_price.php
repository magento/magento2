<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;

$category = Bootstrap::getObjectManager()->create(Category::class);
$category->isObjectNew(true);
$category->setId(4)
    ->setName('Category 1')
    ->setParentId(2)
    ->setPath('1/2/4')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$prices = [11, 12, 23, 29, 100];

$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
$categoryLinkManagement = Bootstrap::getObjectManager()->get(CategoryLinkManagementInterface::class);
foreach ($prices as $price) {
    $product = Bootstrap::getObjectManager()->create(Product::class);
    $product->setTypeId(ProductType::TYPE_SIMPLE)
        ->setAttributeSetId($product->getDefaultAttributeSetId())
        ->setWebsiteIds([1])
        ->setName('Simple with price ' . $price)
        ->setSku('simple_' . $price)
        ->setPrice($price)
        ->setWeight(1)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(
            [
                'use_config_manage_stock' => 1,
                'qty' => 100,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1,
            ]
        );
    $product = $productRepository->save($product);
    $categoryLinkManagement->assignProductToCategories($product->getSku(), [$category->getId()]);
}
