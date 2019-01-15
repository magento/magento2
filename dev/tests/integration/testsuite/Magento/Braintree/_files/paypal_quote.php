<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;

require __DIR__ . '/paypal_vault_token.php';

/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);

/** @var CartInterface $quote */
$quote = $objectManager->get(CartInterface::class);
$quote->setStoreId($storeManager->getStore()->getId())
    ->setCustomerIsGuest(false)
    ->setCustomerId($customer->getId());

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);
