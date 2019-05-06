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
 * Collect and return information about a billing address
 */
class BillingAddressDataProvider
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Address
     */
    private $addressMapper;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * AddressDataProvider constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param Address $addressMapper
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        Address $addressMapper,
        ExtensibleDataObjectConverter $dataObjectConverter
    ) {
        $this->cartRepository = $cartRepository;
        $this->addressMapper = $addressMapper;
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * Collect and return information about a billing addresses
     *
     * @param CartInterface $cart
     * @return null|array
     */
    public function getCartAddresses(CartInterface $cart): ?array
    {
        $cart = $this->cartRepository->get($cart->getId());
        $billingAddress = $cart->getBillingAddress();

        if (!$billingAddress) {
            return null;
        }
        $billingData = $this->dataObjectConverter->toFlatArray($billingAddress, [], AddressInterface::class);
        $addressData = array_merge($billingData, $this->addressMapper->toNestedArray($billingAddress));

        return $addressData;
    }
}
