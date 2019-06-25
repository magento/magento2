<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\ObjectManager;

$objectManager = ObjectManager::getInstance();

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', '100000002')
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

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_rollback.php';
