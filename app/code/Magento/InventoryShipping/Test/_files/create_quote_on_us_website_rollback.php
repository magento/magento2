<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);

$searchCriteria = $searchCriteriaBuilder
    ->addFilter('reserved_order_id', 'created_order_for_test')
    ->create();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CartInterface[] $order */
$carts = $cartRepository->getList($searchCriteria)->getItems();
foreach ($carts as $cart) {
    $cartRepository->delete($cart);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

/* Refresh stores memory cache */
Bootstrap::getObjectManager()->get(StoreManagerInterface::class)->reinitStores();
