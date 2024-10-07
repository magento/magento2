<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Exception\AbstractAggregateException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Create customer and validate address
 */
class CreateCustomerAddress
{
    /**
     * @param AddressInterfaceFactory $addressFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param ValidateAddress $addressValidator
     * @param PopulateCustomerAddressFromInput $populateCustomerAddressFromInput
     */
    public function __construct(
        private readonly AddressInterfaceFactory $addressFactory,
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly ValidateAddress $addressValidator,
        private readonly PopulateCustomerAddressFromInput $populateCustomerAddressFromInput
    ) {
    }

    /**
     * Create customer address
     *
     * @param int $customerId
     * @param array $data
     * @return AddressInterface
     * @throws GraphQlInputException
     */
    public function execute(int $customerId, array $data): AddressInterface
    {
        // It is needed because AddressInterface has country_id field.
        if (isset($data['country_code'])) {
            $data['country_id'] = $data['country_code'];
        }

        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $this->populateCustomerAddressFromInput->execute($address, $data);
        $this->addressValidator->execute($address);
        $address->setCustomerId($customerId);

        try {
            $this->addressRepository->save($address);
        } catch (AbstractAggregateException $e) {
            $errors = $e->getErrors();
            if (is_array($errors) && !empty($errors)) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                $errorMessage = implode("\n", $errorMessages);
            } else {
                $errorMessage = $e->getMessage();
            }
            throw new GraphQlInputException(__($errorMessage), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
        return $address;
    }
}
