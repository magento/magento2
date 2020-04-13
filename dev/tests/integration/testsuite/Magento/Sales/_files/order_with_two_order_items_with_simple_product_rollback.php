<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
/** @var CollectionFactory $orderCollectionFactory */
$orderCollectionFactory = $objectManager->get(CollectionFactory::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$order = $orderCollectionFactory->create()
    ->addFieldToFilter(OrderInterface::INCREMENT_ID, '100000001')
    ->setPageSize(1)
    ->getFirstItem();
if ($order->getId()) {
    $orderRepository->delete($order);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/../../../Magento/Customer/_files/customer_rollback.php';
require __DIR__ . '/../../../Magento/Catalog/_files/products_rollback.php';
