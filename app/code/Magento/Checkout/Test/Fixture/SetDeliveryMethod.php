<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Fixture;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class SetDeliveryMethod implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'cart_id' => null,
        'carrier_code' => 'flatrate',
        'method_code' => 'flatrate'
    ];

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingInformationFactory;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ShippingInformationInterfaceFactory $shippingInformationFactory,
        ShippingInformationManagementInterface $shippingInformationManagement
    ) {
        $this->cartRepository = $cartRepository;
        $this->shippingInformationFactory = $shippingInformationFactory;
        $this->shippingInformationManagement = $shippingInformationManagement;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'cart_id' => (int) Cart ID. Required
     *      'carrier_code' => (string) Carrier Code. Optional
     *      'method_code' => (string) Method Code. Optional
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $cart = $this->cartRepository->get($data['cart_id']);
        /** @var ShippingInformationInterface $shippingInformation */
        $shippingInformation = $this->shippingInformationFactory->create([
            'data' => [
                ShippingInformationInterface::SHIPPING_ADDRESS => $cart->getShippingAddress(),
                ShippingInformationInterface::SHIPPING_CARRIER_CODE => $data['carrier_code'],
                ShippingInformationInterface::SHIPPING_METHOD_CODE => $data['method_code'],
            ],
        ]);
        $this->shippingInformationManagement->saveAddressInformation($cart->getId(), $shippingInformation);

        return null;
    }
}
