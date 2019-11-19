<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

/**
 * Get customer address
 */
class GetCustomerAddress
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
                __('Could not find a address with ID "%address_id"', ['address_id' => $addressId])
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        if ((int)$customerAddress->getCustomerId() !== $customerId) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have permission to address with ID "%address_id"',
                    ['address_id' => $addressId]
                )
            );
        }
        return $customerAddress;
    }
}
