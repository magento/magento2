<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class AddAddressToCart implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        AddressInterface::KEY_TELEPHONE => 3340000000,
        AddressInterface::KEY_POSTCODE => 36104,
        AddressInterface::KEY_COUNTRY_ID => 'US',
        AddressInterface::KEY_CITY => 'Montgomery',
        AddressInterface::KEY_COMPANY => 'Magento',
        AddressInterface::KEY_STREET => ['Green str, 67'],
        AddressInterface::KEY_LASTNAME => 'Doe',
        AddressInterface::KEY_FIRSTNAME => 'John%uniqid%',
        AddressInterface::KEY_REGION_ID => 1,
    ];
    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param ProcessorInterface $dataProcessor
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        ProcessorInterface $dataProcessor,
        AddressInterfaceFactory $addressInterfaceFactory,
        CartRepositoryInterface $cartRepository
    ) {
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->dataProcessor = $dataProcessor;
        $this->cartRepository = $cartRepository;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'cart_id' => (int) Cart ID. Required.
     *      'address' => (array) Address Data. Optional. Default: AddAddressToCart::DEFAULT_DATA
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $cart = $this->cartRepository->get($data['cart_id']);
        $address = $this->addressInterfaceFactory->create(
            [
                'data' => $this->dataProcessor->process($this, array_merge(self::DEFAULT_DATA, $data['address'] ?? []))
            ]
        );
        if (!$cart->getIsMultiShipping()) {
            $cart->setIsMultiShipping(1);
            foreach ($cart->getAllShippingAddresses() as $existingAddress) {
                $cart->removeAddress($existingAddress->getId());
            }
        }
        $address->setCollectShippingRates(true);
        $cart->addShippingAddress($address);

        $this->cartRepository->save($cart);

        return $address;
    }
}
