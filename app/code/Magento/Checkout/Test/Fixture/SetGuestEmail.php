<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class SetGuestEmail implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'cart_id' => null,
        'email' => 'guestuser%uniqid%@example.com'
    ];

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProcessorInterface $dataProcessor
    ) {

        $this->cartRepository = $cartRepository;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'cart_id' => (int) Cart ID. Required
     *      'email' => (string) Guest Email. Optional
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $cart = $this->cartRepository->get($data['cart_id']);
        $data = $this->dataProcessor->process($this, array_merge(self::DEFAULT_DATA, $data));
        $cart->setCustomerEmail($data['email']);
        $this->cartRepository->save($cart);

        return null;
    }
}
