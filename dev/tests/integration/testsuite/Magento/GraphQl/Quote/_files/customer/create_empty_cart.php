<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\TestFramework\Helper\Bootstrap;

/** @var CartManagementInterface $cartManagement */
$cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var QuoteIdMaskFactory $quoteIdMaskFactory */
$quoteIdMaskFactory = Bootstrap::getObjectManager()->get(QuoteIdMaskFactory::class);

$cartId = $cartManagement->createEmptyCartForCustomer(1);
$cart = $cartRepository->get($cartId);
$cart->setReservedOrderId('test_quote');
$cartRepository->save($cart);

/** @var QuoteIdMask $quoteIdMask */
$quoteIdMask = $quoteIdMaskFactory->create();
$quoteIdMask->setQuoteId($cartId)
    ->save();
