<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$quote = $objectManager->get(GetQuoteByReservedOrderId::class)->execute('tsg-123456789');
if ($quote !== null) {
    /** @var CartRepositoryInterface $cartRepository */
    $cartRepository = $objectManager->get(CartRepositoryInterface::class);
    $cartRepository->delete($quote);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
