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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

class GetCustomerAddressV2
{
    /**
     * GetCustomerAddressV2 Constructor
     *
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        private readonly AddressRepositoryInterface $addressRepository
    ) {
    }

    /**
     * Get customer address
     *
     * @param int $addressId
     * @param int $customerId
     * @return AddressInterface
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlAuthorizationException
     */
    public function execute(int $addressId, int $customerId): AddressInterface
    {
        try {
            $customerAddress = $this->addressRepository->getById($addressId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find an address with the specified ID')
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        if ((int)$customerAddress->getCustomerId() !== $customerId) {
            throw new GraphQlAuthorizationException(
                __('Current customer does not have permission to get address with the specified ID')
            );
        }

        return $customerAddress;
    }
}
