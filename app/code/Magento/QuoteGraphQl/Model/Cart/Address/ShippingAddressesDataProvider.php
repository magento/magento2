<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Address;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\QuoteGraphQl\Model\Cart\Address\Mapper\Address;

/**
 * Class AddressDataProvider
 *
 * Collect and return information about cart shipping and billing addresses
 */
class ShippingAddressesDataProvider
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Address
     */
    private $addressMapper;

    /**
     * AddressDataProvider constructor.
     *
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param CartRepositoryInterface $cartRepository
     * @param Address $addressMapper
     */
    public function __construct(
        ExtensibleDataObjectConverter $dataObjectConverter,
        CartRepositoryInterface $cartRepository,
        Address $addressMapper
    ) {
        $this->dataObjectConverter = $dataObjectConverter;
        $this->cartRepository = $cartRepository;
        $this->addressMapper = $addressMapper;
    }

    /**
     * Collect and return information about shipping addresses
     *
     * @param CartInterface $cart
     * @return array
     */
    public function getCartAddresses(CartInterface $cart): array
    {
        $cart = $this->cartRepository->get($cart->getId());
        $addressData = [];
        $shippingAddresses = $cart->getAllShippingAddresses();

        if ($shippingAddresses) {
            foreach ($shippingAddresses as $shippingAddress) {
                $shippingData = $this->dataObjectConverter->toFlatArray($shippingAddress, [], AddressInterface::class);
                $addressData[] = array_merge($shippingData, $this->addressMapper->toNestedArray($shippingAddress));
            }
        }

        return $addressData;
    }
}
