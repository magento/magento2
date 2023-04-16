<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class CustomerCart implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'customer_id' => null
    ];

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartManagementInterface $cartManagement
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'customer_id' => (int) Customer ID. Required.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $cartId = $this->cartManagement->createEmptyCartForCustomer($data['customer_id']);

        return $this->cartRepository->get($cartId);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->cartRepository->delete($data);
    }
}
