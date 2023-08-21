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
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder
    ->addFilter('reserved_order_id', 'multishipping_fpt_quote_id')
    ->create();
/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quotesList = $quoteRepository->getList($searchCriteria)->getItems();

if (!empty($quotesList)) {
    $quote = array_pop($quotesList);
    $quoteRepository->delete($quote);
}

Resolver::getInstance()->requireDataFixture('Magento/Weee/_files/product_with_fpt_rollback.php');
