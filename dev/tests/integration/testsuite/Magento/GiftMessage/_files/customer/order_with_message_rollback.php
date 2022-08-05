<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
use Magento\GiftMessage\Model\Message;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$productRepository->delete($product);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
/** @var OrderInterfaceFactory $orderFactory */
$orderFactory = $objectManager->create(OrderInterfaceFactory::class);
$orders = [];
$orders[] = $orderFactory->create()->loadByIncrementId('999999990');
$orders[] = $orderFactory->create()->loadByIncrementId('999999991');

foreach ($orders as $order) {
    if ($order->getGiftMessageId()) {
        $message = $objectManager->create(Message::class);
        $message->load($order->getGiftMessageId());
        $message->delete();
    }
    if ($order->getId()) {
        $orderRepository->delete($order);
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
