<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;

//test category
$category = Bootstrap::getObjectManager()->create(Category::class);
$category->isObjectNew(true);
$category->setId('565')
    ->setName('c1')
    ->setAttributeSetId('3')
    ->setParentId(2)
    ->setPath('1/2/565')
    ->setLevel('2')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->save();

//virtual product 1
/** @var $product Product */
$product = Bootstrap::getObjectManager()->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Product\Type::TYPE_VIRTUAL)
    ->setId(101)
    ->setAttributeSetId(4)
    ->setName('Virtual Product1')
    ->setSku('virtual1')
    ->setTaxClassId('none')
    ->setDescription('description unique word')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setPrice(10)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([565])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();

//virtual product 2
$product = Bootstrap::getObjectManager()->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Product\Type::TYPE_VIRTUAL)
    ->setId(102)
    ->setAttributeSetId(4)
    ->setName('Virtual Product2')
    ->setSku('virtual2')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setPrice(20)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_IN_CATALOG)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([565])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 50, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();

//virtual product 3
$product = Bootstrap::getObjectManager()->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Product\Type::TYPE_VIRTUAL)
    ->setId(103)
    ->setAttributeSetId(4)
    ->setName('Virtual Product3')
    ->setSku('virtual3')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setPrice(30)
    ->setWeight(1)
    ->setVisibility(Visibility::VISIBILITY_IN_CATALOG)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([565])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 140, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();

//virtual product 4
$product = Bootstrap::getObjectManager()->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Product\Type::TYPE_VIRTUAL)
    ->setId(104)
    ->setAttributeSetId(4)
    ->setName('Virtual Product4')
    ->setSku('virtual4')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setPrice(40)
    ->setWeight(1)
    ->setVisibility(Visibility::VISIBILITY_IN_CATALOG)
    ->setStatus(Status::STATUS_DISABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([565])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 0, 'is_qty_decimal' => 0, 'is_in_stock' => 0])
    ->save();
