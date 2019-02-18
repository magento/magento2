<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Quote\Api\CartRepositoryInterface;

require __DIR__ . '/../_files/paypal_vault_token.php';
require __DIR__ . '/../../Sales/_files/quote_with_customer.php';

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
