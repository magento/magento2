<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

$objectManager = ObjectManager::getInstance();

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', '100000001')
    ->create();

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);

$items = $orderRepository->getList($searchCriteria)->getItems();
$item = reset($items);

if ($item !== false) {
    try {
        $orderRepository->delete($item);
    } catch (NoSuchEntityException $e) {
    }
}

require __DIR__ . '/../_files/full_order_rollback.php';
