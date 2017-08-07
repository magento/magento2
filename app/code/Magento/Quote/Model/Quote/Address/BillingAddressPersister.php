<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Class \Magento\Quote\Model\Quote\Address\BillingAddressPersister
 *
 * @since 2.1.0
 */
class BillingAddressPersister
{
    /**
     * @var QuoteAddressValidator
     * @since 2.1.0
     */
    private $addressValidator;

    /**
     * @var AddressRepositoryInterface
     * @since 2.1.0
     */
    private $addressRepository;

    /**
     * @param QuoteAddressValidator $addressValidator
     * @param AddressRepositoryInterface $addressRepository
     * @since 2.1.0
     */
    public function __construct(
        QuoteAddressValidator $addressValidator,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->addressValidator = $addressValidator;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @param bool $useForShipping
     * @return void
     * @throws NoSuchEntityException
     * @throws InputException
     * @since 2.1.0
     */
    public function save(CartInterface $quote, AddressInterface $address, $useForShipping = false)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $this->addressValidator->validate($address);
        $customerAddressId = $address->getCustomerAddressId();
        $shippingAddress = null;
        $addressData = [];

        if ($useForShipping) {
            $shippingAddress = $address;
        }
        $saveInAddressBook = $address->getSaveInAddressBook() ? 1 : 0;
        if ($customerAddressId) {
            try {
                $addressData = $this->addressRepository->getById($customerAddressId);
            } catch (NoSuchEntityException $e) {
                // do nothing if customer is not found by id
            }
            $address = $quote->getBillingAddress()->importCustomerAddressData($addressData);
            if ($useForShipping) {
                $shippingAddress = $quote->getShippingAddress()->importCustomerAddressData($addressData);
                $shippingAddress->setSaveInAddressBook($saveInAddressBook);
            }
        } elseif ($quote->getCustomerId()) {
            $address->setEmail($quote->getCustomerEmail());
        }
        $address->setSaveInAddressBook($saveInAddressBook);
        $quote->setBillingAddress($address);
        if ($useForShipping) {
            $shippingAddress->setSameAsBilling(1);
            $shippingAddress->setCollectShippingRates(true);
            $quote->setShippingAddress($shippingAddress);
        }
    }
}
