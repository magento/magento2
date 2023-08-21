<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\ProductAlert\Model\PriceFactory;
use Magento\ProductAlert\Model\ResourceModel\Price as PriceResource;
use Magento\ProductAlert\Model\ResourceModel\Stock as StockResource;
use Magento\ProductAlert\Model\StockFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');
Resolver::getInstance()->requireDataFixture('Magento/ProductAlert/_files/product_alert.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$productRepository->cleanCache();
$product = $productRepository->get('simple');
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsiteId = (int)$websiteRepository->get('base')->getId();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->create(CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
/** @var PriceFactory $priceFactory */
$priceFactory = $objectManager->get(PriceFactory::class);
/** @var PriceResource $priceResource */
$priceResource = $objectManager->get(PriceResource::class);
$priceAlert = $priceFactory->create();
$priceAlert->setCustomerId(
    $customer->getId()
)->setProductId(
    $product->getId()
)->setPrice(
    $product->getPrice()+1
)->setWebsiteId(
    $baseWebsiteId
);
$priceResource->save($priceAlert);
/** @var StockFactory $stockFactory */
$stockFactory = $objectManager->get(StockFactory::class);
/** @var StockResource $stockResource */
$stockResource = $objectManager->get(StockResource::class);
$stockAlert = $stockFactory->create();
$stockAlert->setCustomerId(
    $customer->getId()
)->setProductId(
    $product->getId()
)->setWebsiteId(
    $baseWebsiteId
);
$stockResource->save($stockAlert);
