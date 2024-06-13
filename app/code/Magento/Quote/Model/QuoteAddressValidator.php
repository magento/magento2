<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Quote shipping/billing address validator service.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteAddressValidator
{
    /**
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var Session
     * @deprecated 101.1.1 This class is not a part of HTML presentation layer and should not use sessions.
     * @see Session
     */
    protected Session $customerSession;

    /**
     * Constructs a quote shipping address validator service object.
     *
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerRepositoryInterface $customerRepository Customer repository.
     * @param Session $customerSession
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession
    ) {
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * Validate address.
     *
     * @param AddressInterface $address
     * @param int|null $customerId
     * @return void
     * @throws LocalizedException The specified customer ID or address ID is not valid.
     * @throws NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    private function doValidate(AddressInterface $address, ?int $customerId): void
    {
        $customerAddressId = $address->getCustomerAddressId();
        if ($customerAddressId) {
            //Existing address cannot belong to a guest
            if (!$customerId) {
                throw new NoSuchEntityException(
                    __('Invalid customer address id %1', $customerAddressId)
                );
            }

            $customer = $this->customerRepository->getById($customerId);

            //Validating address ID
            $this->addressRepository->getById($customerAddressId);

            //Finding available customer's addresses
            $applicableAddressIds = array_map(function (CustomerAddress $address) {
                return $address->getId();
            }, $customer->getAddresses());

            if (!in_array($customerAddressId, $applicableAddressIds)) {
                throw new NoSuchEntityException(
                    __('Invalid customer address id %1', $customerAddressId)
                );
            }
        }
    }

    /**
     * Validates the fields in a specified address data object.
     *
     * @param AddressInterface $addressData The address data object.
     * @return bool
     * @throws InputException The specified address belongs to another customer.
     * @throws NoSuchEntityException|LocalizedException The specified customer ID or address ID is not valid.
     */
    public function validate(AddressInterface $addressData): bool
    {
        $this->doValidate($addressData, $addressData->getCustomerId());

        return true;
    }

    /**
     * Validate address to be used for cart.
     *
     * @param CartInterface $cart
     * @param AddressInterface $address
     * @return void
     * @throws InputException The specified address belongs to another customer.
     * @throws NoSuchEntityException|LocalizedException The specified customer ID or address ID is not valid.
     */
    public function validateForCart(CartInterface $cart, AddressInterface $address): void
    {
        $this->doValidate($address, $cart->getCustomerIsGuest() ? null : (int) $cart->getCustomer()->getId());
    }

    /**
     * Validate address id to be used for cart.
     *
     * @param CartInterface $cart
     * @param AddressInterface $address
     * @return void
     * @throws NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    public function validateWithExistingAddress(CartInterface $cart, AddressInterface $address): void
    {
        // check if address belongs to quote.
        if ($address->getId() !== null) {
            $old = $cart->getAddressById($address->getId());
            if (empty($old)) {
                throw new NoSuchEntityException(
                    __('Invalid quote address id %1', $address->getId())
                );
            }
        }
    }
}
