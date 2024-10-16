<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
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
        //validate customer id
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if (!$customer->getId()) {
                throw new NoSuchEntityException(
                    __('Invalid customer id %1', $customerId)
                );
            }
        }

        if ($address->getCustomerAddressId()) {
            //Existing address cannot belong to a guest
            if (!$customerId) {
                throw new NoSuchEntityException(
                    __('Invalid customer address id %1', $address->getCustomerAddressId())
                );
            }
            //Validating address ID
            try {
                $this->addressRepository->getById($address->getCustomerAddressId());
            } catch (NoSuchEntityException $e) {
                throw new NoSuchEntityException(
                    __('Invalid address id %1', $address->getId())
                );
            }
            //Finding available customer's addresses
            $applicableAddressIds = array_map(function ($address) {
                /** @var \Magento\Customer\Api\Data\AddressInterface $address */
                return $address->getId();
            }, $this->customerRepository->getById($customerId)->getAddresses());
            if (!in_array($address->getCustomerAddressId(), $applicableAddressIds)) {
                throw new NoSuchEntityException(
                    __('Invalid customer address id %1', $address->getCustomerAddressId())
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
