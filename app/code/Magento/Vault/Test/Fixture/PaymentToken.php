<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Vault\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\PaymentTokenFactory;

/**
 * Create a payment token for a customer
 *
 * Example: Basic usage. This will create a payment token for a customer
 * ```php
 *    #[
 *        DataFixture(CustomerFixture::class, as: 'customer'),
 *        DataFixture(PaymentTokenFixture::class, ['customer_id' => '$customer.id$'], as: 'token'),
 *        DataFixture(
 *            SetPaymentMethodFixture::class,
 *            [
 *                'cart_id' => '$cart.id$',
 *                'method' => [
 *                    'method' => 'payflowpro_cc_vault',
 *                    'additional_data' => [
 *                        'public_hash' => '$token.public_hash$',
 *                        'customer_id' => '$customer.id$',
 *                    ]
 *                ]
 *            ]
 *        )
 *    ]
 * ```
 */
class PaymentToken implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'entity_id' => null,
        'customer_id' => null,
        'website_id' => null,
        'public_hash' => null,
        'payment_method_code' => null,
        'type' => PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD,
        'expires_at' => null,
        'created_at' => null,
        'gateway_token' => null,
        'token_details' => null,
        'is_visible' => true,
        'is_active' => true,
    ];

    /**
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        private readonly PaymentTokenRepositoryInterface $paymentTokenRepository,
        private readonly PaymentTokenFactory $paymentTokenFactory,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(
            self::DEFAULT_DATA,
            [
                'expires_at' => strtotime('+1 year'),
                'public_hash' => $this->encryptor->hash(uniqid((string) $data['customer_id']))
            ],
            $data
        );
        $token = $this->paymentTokenFactory->create($data['type']);
        $token->addData($data);
        $this->paymentTokenRepository->save($token);
        return $token;
    }
}
