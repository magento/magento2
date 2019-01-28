<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

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
     * Address factory.
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Customer repository.
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @deprecated This class is not a part of HTML presentation layer and should not use sessions.
     */
    protected $customerSession;

    /**
     * Constructs a quote shipping address validator service object.
     *
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository Customer repository.
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * Validate address.
     *
     * @param AddressInterface $address
     * @param int|null $customerId Cart belongs to
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    private function doValidate(AddressInterface $address, $customerId)
    {
        //validate customer id
        if ($customerId) {
            try {
                $this->customerRepository->getById($customerId);
            } catch (NoSuchEntityException $exception) {
                throw new NoSuchEntityException(__('Invalid customer id %1', $customerId));
            }
        }

        if ($address->getCustomerAddressId()) {
            //Existing address cannot belong to a guest
            if (!$customerId) {
                throw new NoSuchEntityException(__('Invalid customer address id %1', $address->getCustomerAddressId()));
            }
            //Validating address ID
            try {
                $this->addressRepository->getById($address->getCustomerAddressId());
            } catch (NoSuchEntityException $e) {
                throw new NoSuchEntityException(__('Invalid address id %1', $address->getId()));
            }
            //Finding available customer's addresses
            $applicableAddressIds = array_map(function ($address) {
                /** @var \Magento\Customer\Api\Data\AddressInterface $address */
                return $address->getId();
            }, $this->customerRepository->getById($customerId)->getAddresses());
            if (!in_array($address->getCustomerAddressId(), $applicableAddressIds)) {
                throw new NoSuchEntityException(__('Invalid customer address id %1', $address->getCustomerAddressId()));
            }
        }
    }

    /**
     * Validates the fields in a specified address data object.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $addressData The address data object.
     * @return bool
     */
    public function validate(AddressInterface $addressData)
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
     */
    public function validateForCart(CartInterface $cart, AddressInterface $address)
    {
        $this->doValidate($address, $cart->getCustomerIsGuest() ? null : $cart->getCustomer()->getId());
    }
}
