<?php
/**
 * Creates a simple product to be used for test cases.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

Bootstrap::getInstance()->reinitialize();

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);

/** @var $product Product */
$product = $objectManager->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setId(77)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple_77')
    ->setPrice(10)
    ->setWeight(1)
    ->setShortDescription("Short description")
    ->setTaxClassId(0)
    ->setTierPrice(
        [
            [
                'website_id' => 0,
                'cust_group' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'price_qty'  => 2,
                'price'      => 8,
            ],
            [
                'website_id' => 0,
                'cust_group' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'price_qty'  => 5,
                'price'      => 5,
            ],
            [
                'website_id' => 0,
                'cust_group' => \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID,
                'price_qty'  => 3,
                'price'      => 5,
            ],
        ]
    )
    ->setDescription('Description with <b>html tag</b>')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
        ]
    )->setCanSaveCustomOptions(true)
    ->setHasOptions(true)
    ->setCustomAttribute('test_configurable', 42);

/** @var ProductRepositoryInterface $productRepositoryFactory */
$productRepositoryFactory = $objectManager->create(ProductRepositoryInterface::class);
$productRepositoryFactory->save($product);
