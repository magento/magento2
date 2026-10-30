<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class DeleteCustomerAddressV2
{
    /**
     * DeleteCustomerAddressV2 Constructor
     *
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        private readonly AddressRepositoryInterface $addressRepository
    ) {
    }

    /**
     * Delete customer address
     *
     * @param AddressInterface $address
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(AddressInterface $address): void
    {
        if ($address->isDefaultBilling()) {
            throw new GraphQlInputException(
                __('Customer Address with the specified ID is set as default billing address and can not be deleted')
            );
        }

        if ($address->isDefaultShipping()) {
            throw new GraphQlInputException(
                __('Customer Address with the specified ID is set as default shipping address and can not be deleted')
            );
        }

        try {
            $this->addressRepository->delete($address);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
