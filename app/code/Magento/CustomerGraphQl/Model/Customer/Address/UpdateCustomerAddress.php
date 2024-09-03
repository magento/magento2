<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\AbstractAggregateException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Update customer address on the existing customer address
 */
class UpdateCustomerAddress
{
    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param ValidateAddress $addressValidator
     * @param PopulateCustomerAddressFromInput $populateCustomerAddressFromInput
     * @param array $restrictedKeys
     */
    public function __construct(
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly DataObjectHelper $dataObjectHelper,
        private readonly ValidateAddress $addressValidator,
        private readonly PopulateCustomerAddressFromInput $populateCustomerAddressFromInput,
        private readonly array $restrictedKeys = []
    ) {
    }

    /**
     * Update customer address
     *
     * @param AddressInterface $address
     * @param array $data
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(AddressInterface $address, array $data): void
    {
        if (isset($data['country_code'])) {
            $data['country_id'] = $data['country_code'];
        }

        $filteredData = array_diff_key($data, array_flip($this->restrictedKeys));
        $this->dataObjectHelper->populateWithArray($address, $filteredData, AddressInterface::class);

        if (!empty($data['region']['region_id'])) {
            $address->setRegionId($address->getRegion()->getRegionId());
        } else {
            $data['region']['region_id'] = null;
        }

        $this->populateCustomerAddressFromInput->execute($address, $filteredData);
        $this->addressValidator->execute($address);

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
    }
}
