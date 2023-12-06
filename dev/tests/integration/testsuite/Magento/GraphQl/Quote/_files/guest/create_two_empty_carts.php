<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
/** @var MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId1 */
$maskedQuoteIdToQuoteId1 = Bootstrap::getObjectManager()->get(MaskedQuoteIdToQuoteIdInterface::class);
$cartHash1 = $guestCartManagement->createEmptyCart();
$cartId1 = $maskedQuoteIdToQuoteId1->execute($cartHash1);
$cart1 = $cartRepository->get($cartId1);
$cart1->setReservedOrderId('test_quote1');
$cartRepository->save($cart1);
/** @var MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId2 */
$maskedQuoteIdToQuoteId2 = Bootstrap::getObjectManager()->get(MaskedQuoteIdToQuoteIdInterface::class);
$cartHash2 = $guestCartManagement->createEmptyCart();
$cartId2 = $maskedQuoteIdToQuoteId2->execute($cartHash2);
$cart2 = $cartRepository->get($cartId2);
$cart2->setReservedOrderId('test_quote2');
$cartRepository->save($cart2);
