<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$defaultWebsiteId = (int) $storeManager->getWebsite('base')->getId();
$websiteId = (int) $storeManager->getWebsite('test')->getId();
$storeId = (int) $storeManager->getStore('fixture_second_store')->getId();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
$product = $productRepository->get('simple');
$product->setWebsiteIds([$defaultWebsiteId, $websiteId]);
$productRepository->save($product);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$order->setShippingMethod('flatrate_flatrate')->setStoreId($storeId);
$orderRepository->save($order);
