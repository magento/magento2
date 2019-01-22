<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Get customer address for user
 */
class GetCustomerAddressForUser
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(AddressRepositoryInterface $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * Get customer address for user
     *
     * @param int $addressId
     * @param int $userId
     * @return AddressInterface
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(int $addressId, int $userId): AddressInterface
    {
        try {
            /** @var AddressInterface $address */
            $address = $this->addressRepository->getById($addressId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Address id %1 does not exist.', [$addressId])
            );
        }

        if ($address->getCustomerId() != $userId) {
            throw new GraphQlAuthorizationException(
                __('Current customer does not have permission to address id %1', [$addressId])
            );
        }
        return $address;
    }
}
