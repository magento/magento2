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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Delete customer address
 */
class DeleteCustomerAddress
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository
    ) {
        $this->addressRepository = $addressRepository;
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
                __('Customer Address %1 is set as default billing address and can not be deleted', [$address->getId()])
            );
        }
        if ($address->isDefaultShipping()) {
            throw new GraphQlInputException(
                __('Customer Address %1 is set as default shipping address and can not be deleted', [$address->getId()])
            );
        }

        try {
            $this->addressRepository->delete($address);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
