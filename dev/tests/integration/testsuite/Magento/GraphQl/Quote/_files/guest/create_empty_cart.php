<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var GuestCartManagementInterface $guestCartManagement */
$guestCartManagement = Bootstrap::getObjectManager()->get(GuestCartManagementInterface::class);
/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId */
$maskedQuoteIdToQuoteId = Bootstrap::getObjectManager()->get(MaskedQuoteIdToQuoteIdInterface::class);

$cartHash = $guestCartManagement->createEmptyCart();
$cartId = $maskedQuoteIdToQuoteId->execute($cartHash);
$cart = $cartRepository->get($cartId);
$cart->setReservedOrderId('test_quote');
$cartRepository->save($cart);
