<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteAddressValidator;

/**
 * Saves billing address for quotes.
 */
class BillingAddressPersister
{
    /**
     * @var QuoteAddressValidator
     */
    private $addressValidator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param QuoteAddressValidator $addressValidator
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        QuoteAddressValidator $addressValidator,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->addressValidator = $addressValidator;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Save address for billing.
     *
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @param bool $useForShipping
     * @return void
     * @throws NoSuchEntityException
     * @throws InputException|LocalizedException
     */
    public function save(CartInterface $quote, AddressInterface $address, $useForShipping = false)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $this->addressValidator->validateForCart($quote, $address);
        $customerAddressId = $address->getCustomerAddressId();
        $shippingAddress = null;
        if ($useForShipping) {
            $shippingAddress = $address;
        }
        $saveInAddressBook = $address->getSaveInAddressBook() ? 1 : 0;
        if ($customerAddressId) {
            try {
                $addressData = $this->addressRepository->getById($customerAddressId);
                $address = $quote->getBillingAddress()->importCustomerAddressData($addressData);
                if ($useForShipping) {
                    $shippingAddress = $quote->getShippingAddress()->importCustomerAddressData($addressData);
                    $shippingAddress->setSaveInAddressBook($saveInAddressBook);
                }
            } catch (NoSuchEntityException $e) {
                $address->setCustomerAddressId(null);
            }
        } elseif ($quote->getCustomerId() && !$address->getEmail()) {
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
