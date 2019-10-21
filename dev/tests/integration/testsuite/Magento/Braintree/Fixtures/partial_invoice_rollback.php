<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\TestFramework\ObjectManager;

$objectManager = ObjectManager::getInstance();

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', '%10000000%', 'like')
    ->create();

/** @var InvoiceRepositoryInterface $invoiceRepository */
$invoiceRepository = $objectManager->get(InvoiceRepositoryInterface::class);
$items = $invoiceRepository->getList($searchCriteria)
    ->getItems();

foreach ($items as $item) {
    $invoiceRepository->delete($item);
}

require __DIR__ . '/order_rollback.php';
