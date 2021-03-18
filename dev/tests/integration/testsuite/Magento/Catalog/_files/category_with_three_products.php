<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\Store;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_with_different_price_products.php');

$objectManager = Bootstrap::getObjectManager();
/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->create(GetCategoryByName::class);
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setStoreId(Store::DEFAULT_STORE_ID)
    ->setWebsiteIds([1])
    ->setName('Simple Product2')
    ->setSku('simple1002')
    ->setPrice(10)
    ->setWeight(1)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setCategoryIds([$getCategoryByName->execute('Category 999')->getId()])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);
$productRepository->save($product);
