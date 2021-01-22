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
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     */
    public function generateMockSaleResponse(array $attributes)
    {
        if (empty($attributes['paymentMethodNonce'])) {
            return new \Braintree\Result\Error(
                [
                    'errors' => [
                        [
                            'errorData' => [
                                'code' => 2019,
                                'message' => 'Your transaction has been declined.'
                            ]
                        ]
                    ],
                    'transaction' => $this->createTransaction($attributes)->jsonSerialize(),
                ]
            );
        }

        $transaction = $this->createTransaction($attributes);

        return new \Braintree\Result\Successful([$transaction]);
    }

    /**
     * Create mock nonce response for testing
     *
     * @param string $token
     * @return \Braintree\Instance
     */
    public function generateMockNonceResponse(string $token): \Braintree\Instance
    {
        $nonce = $this->createNonce($token);

        return new \Braintree\Result\Successful($nonce, 'paymentMethodNonce');
    }

    /**
     * Create mock client token
     *
     * @return string
     */
    public function generateMockClientToken(): string
    {
        return $this->random->getRandomString(32);
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
        return \Braintree\Transaction::factory(
            [
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
            ]
        );
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
            'expirationYear' => date('Y'),
            'last4' => (string) random_int(1000, 9999),
            'token' => $this->random->getRandomString(6),
            'uniqueNumberIdentifier' => $this->random->getRandomString(32),
        ];
    }

    /**
     * Create fake Braintree nonce
     *
     * @param string $token
     * @return \Braintree\PaymentMethodNonce
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createNonce(string $token): \Braintree\PaymentMethodNonce
    {
        $lastFour = (string) random_int(1000, 9999);
        $lastTwo = substr($lastFour, -2);
        return \Braintree\PaymentMethodNonce::factory(
            [
                'bin' => $token,
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
            ]
        );
    }
}
