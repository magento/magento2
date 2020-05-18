<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Vault\Model\PaymentTokenManagement;

Resolver::getInstance()->requireDataFixture('Magento/Braintree/_files/paypal_vault_token.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/quote_with_customer.php');

$objectManager = Bootstrap::getObjectManager();
/** @var PaymentTokenManagement $tokenManagement */
$tokenManagement = $objectManager->get(PaymentTokenManagement::class);
$paymentToken = $tokenManagement->getByGatewayToken('mx29vk', ConfigProvider::PAYPAL_CODE, 1);
/** @var $quote Quote */
$quote = $objectManager->create(Quote::class);
$quote->load('test01', 'reserved_order_id');
$quote->getShippingAddress()
    ->setShippingMethod('flatrate_flatrate')
    ->setCollectShippingRates(true);
$quote->getPayment()
    ->setMethod(ConfigProvider::PAYPAL_VAULT_CODE)
    ->setAdditionalInformation(
        [
            'customer_id' => $quote->getCustomerId(),
            'public_hash' => $paymentToken->getPublicHash()
        ]
    );

$quote->collectTotals();

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);
