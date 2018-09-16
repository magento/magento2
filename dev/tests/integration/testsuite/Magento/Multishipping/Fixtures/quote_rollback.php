<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

require __DIR__ . '/items_rollback.php';

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', 'multishipping_quote_id')
    ->create();

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$items = $quoteRepository->getList($searchCriteria)->getItems();

if (!empty($items)) {
    $quote = array_pop($items);
    $quoteRepository->delete($quote);
}
