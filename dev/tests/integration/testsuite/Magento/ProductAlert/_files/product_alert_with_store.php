<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\ProductAlert\Model\Price;
use Magento\ProductAlert\Model\Stock;
use Magento\Store\Api\StoreRepositoryInterface;

require __DIR__ . '/../../../Magento/Customer/_files/customer_for_second_store.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_out_of_stock_without_categories.php';

$objectManager = Bootstrap::getObjectManager();
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
