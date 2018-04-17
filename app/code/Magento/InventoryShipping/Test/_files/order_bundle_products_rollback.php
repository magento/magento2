<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
/** @var OrderManagementInterface $orderManagement */
$orderManagement = Bootstrap::getObjectManager()->get(OrderManagementInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder
    ->addFilter('increment_id', 'test_order_bundle_1')
    ->create();
/** @var OrderInterface $order */
$order = current($orderRepository->getList($searchCriteria)->getItems());
if ($order) {
    $orderManagement->cancel($order->getEntityId());
    $orderRepository->delete($order);
}
