<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\TestFramework\Downloadable\Model\RemoveLinkPurchasedByOrderIncrementId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Downloadable/_files/product_downloadable_with_purchased_separately_links_rollback.php'
);
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_rollback.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var RemoveLinkPurchasedByOrderIncrementId $removeLinkPurchasedByOrderIncrementId */
$removeLinkPurchasedByOrderIncrementId = $objectManager->get(RemoveLinkPurchasedByOrderIncrementId::class);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderIncrementIdToDelete = '100000002';
$removeLinkPurchasedByOrderIncrementId->execute($orderIncrementIdToDelete);
/** @var OrderFactory $order */
$order = $objectManager->get(OrderFactory::class)->create();
$order->loadByIncrementId($orderIncrementIdToDelete);

if ($order->getId()) {
    $orderRepository->delete($order);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
