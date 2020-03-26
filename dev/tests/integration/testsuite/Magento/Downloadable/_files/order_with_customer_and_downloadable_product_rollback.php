<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\TestFramework\Downloadable\Model\RemoveLinkPurchasedByOrderIncrementId;

require __DIR__ . '/../../../Magento/Customer/_files/customer_rollback.php';
require __DIR__ . '/../../../Magento/Downloadable/_files/product_downloadable_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var RemoveLinkPurchasedByOrderIncrementId $removeLinkPurchasedByOrderIncrementId */
$removeLinkPurchasedByOrderIncrementId = $objectManager->get(RemoveLinkPurchasedByOrderIncrementId::class);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderIncrementIdToDelete = '100000001';
$removeLinkPurchasedByOrderIncrementId->execute($orderIncrementIdToDelete);
/** @var OrderFactory $order */
$order = $objectManager->get(OrderFactory::class)->create();
$order->loadByIncrementId($orderIncrementIdToDelete);

if ($order->getId()) {
    $orderRepository->delete($order);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
