<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$quote = $objectManager->get(GetQuoteByReservedOrderId::class)->execute('customer_quote_product_custom_options');
if ($quote !== null) {
    /** @var CartRepositoryInterface $quoteRepository */
    $quoteRepository = $objectManager->get(CartRepositoryInterface::class);
    $quoteRepository->delete($quote);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_with_uk_address_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_with_options_rollback.php');
