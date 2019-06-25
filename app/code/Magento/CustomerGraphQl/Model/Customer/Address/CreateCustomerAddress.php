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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Create customer address
 */
class CreateCustomerAddress
{
    /**
     * @var GetAllowedAddressAttributes
     */
    private $getAllowedAddressAttributes;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param GetAllowedAddressAttributes $getAllowedAddressAttributes
     * @param AddressInterfaceFactory $addressFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        GetAllowedAddressAttributes $getAllowedAddressAttributes,
        AddressInterfaceFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->getAllowedAddressAttributes = $getAllowedAddressAttributes;
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->dataObjectHelper = $dataObjectHelper;
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
        $this->validateData($data);

        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $this->dataObjectHelper->populateWithArray($address, $data, AddressInterface::class);

        if (isset($data['region']['region_id'])) {
            $address->setRegionId($address->getRegion()->getRegionId());
        }
        $address->setCustomerId($customerId);

        try {
            $this->addressRepository->save($address);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
        return $address;
    }

    /**
     * Validate customer address create data
     *
     * @param array $addressData
     * @return void
     * @throws GraphQlInputException
     */
    public function validateData(array $addressData): void
    {
        $attributes = $this->getAllowedAddressAttributes->execute();
        $errorInput = [];

        foreach ($attributes as $attributeName => $attributeInfo) {
            if ($attributeInfo->getIsRequired()
                && (!isset($addressData[$attributeName]) || empty($addressData[$attributeName]))
            ) {
                $errorInput[] = $attributeName;
            }
        }

        if ($errorInput) {
            throw new GraphQlInputException(
                __('Required parameters are missing: %1', [implode(', ', $errorInput)])
            );
        }
    }
}
