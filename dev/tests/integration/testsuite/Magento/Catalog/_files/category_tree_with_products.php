<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\Config;

$objectManager = Bootstrap::getObjectManager();
$categoryFactory = $objectManager->get(CategoryInterfaceFactory::class);
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

$categoryA = $categoryFactory->create(
    [
        'data' => [
            CategoryInterface::KEY_NAME => 'Category A',
            CategoryInterface::KEY_PARENT_ID => 2,
            CategoryInterface::KEY_LEVEL => 2,
            CategoryInterface::KEY_POSITION => 1,
            CategoryInterface::KEY_IS_ACTIVE => true,
            CategoryInterface::KEY_AVAILABLE_SORT_BY =>['position', 'name'],
            'default_sort_by' => 'name',
        ],
    ]
);
$categoryA->isObjectNew(true);
$categoryA = $categoryRepository->save($categoryA);

$categoryB = $categoryFactory->create(
    [
        'data' => [
            CategoryInterface::KEY_NAME => 'Category B',
            CategoryInterface::KEY_PARENT_ID => 2,
            CategoryInterface::KEY_LEVEL => 2,
            CategoryInterface::KEY_POSITION => 1,
            CategoryInterface::KEY_IS_ACTIVE => true,
            CategoryInterface::KEY_AVAILABLE_SORT_BY =>['position', 'name'],
            'default_sort_by' => 'name',
        ],
    ]
);
$categoryB->isObjectNew(true);
$categoryB = $categoryRepository->save($categoryB);

$categoryC = $categoryFactory->create(
    [
        'data' => [
            CategoryInterface::KEY_NAME => 'Category C',
            CategoryInterface::KEY_PARENT_ID => $categoryB->getId(),
            CategoryInterface::KEY_LEVEL => 2,
            CategoryInterface::KEY_POSITION => 1,
            CategoryInterface::KEY_IS_ACTIVE => true,
            CategoryInterface::KEY_AVAILABLE_SORT_BY =>['position', 'name'],
            'default_sort_by' => 'name',
        ],
    ]
);
$categoryC->isObjectNew(true);
$categoryC = $categoryRepository->save($categoryC);

$defaultAttributeSet = $objectManager->get(Config::class)
    ->getEntityType('catalog_product')
    ->getDefaultAttributeSetId();
$product = $productFactory->create(
    [
        'data' => [
            'type_id' => Type::TYPE_SIMPLE,
            'attribute_set_id' => $defaultAttributeSet,
            'store_id' => Store::DEFAULT_STORE_ID,
            'website_ids' => [1],
            'name' => 'Simple Product B',
            'sku' => 'simpleB',
            'price' => 10,
            'weight' => 1,
            'stock_data' => ['use_config_manage_stock' => 0],
            'category_ids' => [$categoryB->getId()],
            'visibility' => Visibility::VISIBILITY_BOTH,
            'status' => Status::STATUS_ENABLED,
        ],
    ]
);
$productRepository->save($product);

$product = $productFactory->create(
    [
        'data' => [
            'type_id' => Type::TYPE_SIMPLE,
            'attribute_set_id' => $defaultAttributeSet,
            'store_id' => Store::DEFAULT_STORE_ID,
            'website_ids' => [1],
            'name' => 'Simple Product C',
            'sku' => 'simpleC',
            'price' => 20,
            'weight' => 1,
            'stock_data' => ['use_config_manage_stock' => 0],
            'category_ids' => [$categoryC->getId()],
            'visibility' => Visibility::VISIBILITY_BOTH,
            'status' => Status::STATUS_ENABLED,
        ],
    ]
);
$productRepository->save($product);
