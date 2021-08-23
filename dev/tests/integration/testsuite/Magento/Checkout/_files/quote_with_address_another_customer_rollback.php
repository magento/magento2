<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_with_addresses_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_rollback.php');

$objectManager = Bootstrap::getObjectManager();
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', 'test_order_999')->create();

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$items = $quoteRepository->getList($searchCriteria)->getItems();
$item = array_pop($items);
$quoteRepository->delete($item);
