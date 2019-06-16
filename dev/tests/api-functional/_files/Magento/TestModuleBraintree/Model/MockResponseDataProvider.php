<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleBraintree\Model;

use Magento\Framework\Math\Random;

/**
 * Provide mock responses for Braintree adapter
 */
class MockResponseDataProvider
{
    /**
     * @var Random
     */
    private $random;

    /**
     * @param Random $random
     */
    public function __construct(
        Random $random
    ) {
        $this->random = $random;
    }

    /**
     * Create mock sale response for testing
     *
     * @param array $attributes
     * @return \Braintree\Instance
     */
    public function generateMockSaleResponse(array $attributes): \Braintree\Instance
    {
        $transaction = $this->createTransaction($attributes);

        return new \Braintree\Result\Successful([$transaction]);
    }

    /**
     * Create mock nonce response for testing
     *
     * @return \Braintree\Instance
     */
    public function generateMockNonceResponse(): \Braintree\Instance
    {
        $nonce = $this->createNonce();

        return new \Braintree\Result\Successful($nonce, 'paymentMethodNonce');
    }

    /**
     * Create Braintree transaction from provided request attributes
     *
     * @param array $attributes
     * @return \Braintree\Transaction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createTransaction(array $attributes): \Braintree\Transaction
    {
        $creditCardInfo = $this->generateCardDetails();
        return \Braintree\Transaction::factory([
            'amount' => $attributes['amount'],
            'billing' => $attributes['billing'] ?? null,
            'creditCard' => $creditCardInfo,
            'cardDetails' => new \Braintree\Transaction\CreditCardDetails($creditCardInfo),
            'currencyIsoCode' => 'USD',
            'customer' => $attributes['customer'],
            'cvvResponseCode' => 'M',
            'id' => $this->random->getRandomString(8),
            'options' => $attributes['options'] ?? null,
            'shipping' => $attributes['shipping'] ?? null,
            'paymentMethodNonce' => $attributes['paymentMethodNonce'],
            'status' => 'authorized',
            'type' => 'sale',
        ]);
    }

    /**
     * Generate fake Braintree card details
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function generateCardDetails(): array
    {
        return [
            'bin' => $this->random->getRandomString(6),
            'cardType' => 'Visa',
            'expirationMonth' => '12',
            'expirationYear' => '2020', //TODO: make dynamic
            'last4' => (string) random_int(1000, 9999),
            'token' => $this->random->getRandomString(6),
            'uniqueNumberIdentifier' => $this->random->getRandomString(32),
        ];
    }

    /**
     * Create fake Braintree nonce
     *
     * @return \Braintree\PaymentMethodNonce
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createNonce(): \Braintree\PaymentMethodNonce
    {
        $lastFour = (string) random_int(1000, 9999);
        $lastTwo = substr($lastFour, -2);
        return \Braintree\PaymentMethodNonce::factory([
            'consumed' => false,
            'default' => true,
            'description' => 'ending in ' . $lastTwo,
            'details' => [
                'bin' => $this->random->getRandomString(6),
                'cardType' => 'Visa',
                'lastFour' => $lastFour,
                'lastTwo' => $lastTwo,
            ],
            'hasSubscription' => false,
            'isLocked' => false,
            'nonce' => $this->random->getRandomString(36),
            'type' => 'CreditCard'
        ]);
    }
}
