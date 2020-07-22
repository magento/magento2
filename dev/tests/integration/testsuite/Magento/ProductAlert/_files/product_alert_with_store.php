<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\ProductAlert\Model\Price;
use Magento\ProductAlert\Model\Stock;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_for_second_store.php');
Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/product_simple_out_of_stock_without_categories.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = Bootstrap::getObjectManager()->create(CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$storeRepository = $objectManager->get(StoreRepositoryInterface::class);
$storeId = $storeRepository->get('fixture_second_store')->getId();

$price = $objectManager->create(Price::class);
$price->setCustomerId($customer->getId())
    ->setProductId($product->getId())
    ->setPrice($product->getPrice()+1)
    ->setWebsiteId(1)
    ->setStoreId($storeId);
$price->save();

$stock = $objectManager->create(Stock::class);
$stock->setCustomerId($customer->getId())
    ->setProductId($product->getId())
    ->setWebsiteId(1)
    ->setStoreId($storeId);
$stock->save();
