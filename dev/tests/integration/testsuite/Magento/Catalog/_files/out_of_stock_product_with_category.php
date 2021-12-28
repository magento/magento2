<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
$product = $productFactory->create();
$product->isObjectNew(true);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Simple Product Out Of Stock With Category')
    ->setSku('out-of-stock-product')
    ->setPrice(10)
    ->setWeight(1)
    ->setShortDescription("Short description")
    ->setTaxClassId(0)
    ->setDescription('Description with <b>html tag</b>')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setCategoryIds([333])
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 0,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 0,
        ]
    )
    ->setCanSaveCustomOptions(true)
    ->setHasOptions(true);
/** @var ProductRepositoryInterface $productRepositoryFactory */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->save($product);
