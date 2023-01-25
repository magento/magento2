<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\TestFramework\Helper\Bootstrap;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');
$product = $productRepository->get('simple');
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_duplicated.php');
$product = $productRepository->get('simple-1');
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_with_full_option_set.php');
$product = $productRepository->get('simple');
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_with_url_key.php');
$product = $productRepository->get('simple2');
$orderItems[] = [
        'product_id' => $product->getId(),
        'base_price' => 123,
        'order_id' => $order->getId(),
        'price' => 123,
        'row_total' => 126,
        'product_type' => 'simple'
];
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_with_all_fields.php');
$product = $productRepository->get('simple');
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_with_custom_attribute.php');
$product = $productRepository->get('simple');
$orderItems[] = [
    'product_id' => $product->getId(),
    'base_price' => 123,
    'order_id' => $order->getId(),
    'price' => 123,
    'row_total' => 126,
    'product_type' => 'simple'
];

/** @var array $orderItemData */
foreach ($orderItems as $orderItemData) {
    /** @var $orderItem \Magento\Sales\Model\Order\Item */
    $orderItem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Sales\Model\Order\Item::class
    );
    $orderItem
        ->setData($orderItemData)
        ->save();
}
