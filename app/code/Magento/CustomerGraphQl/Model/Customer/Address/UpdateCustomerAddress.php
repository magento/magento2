<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Update customer address on the existing customer address
 */
class UpdateCustomerAddress
{
    /**
     * @var GetAllowedAddressAttributes
     */
    private $getAllowedAddressAttributes;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var array
     */
    private $restrictedKeys;

    /**
     * @var DirectoryData
     */
    private $directoryData;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @var ValidateAddress
     */
    private $addressValidator;

    /**
     * @var PopulateCustomerAddressFromInput
     */
    private $populateCustomerAddressFromInput;

    /**
     * @param GetAllowedAddressAttributes $getAllowedAddressAttributes
     * @param AddressRepositoryInterface $addressRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param DirectoryData $directoryData
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param ValidateAddress $addressValidator
     * @param PopulateCustomerAddressFromInput $populateCustomerAddressFromInput
     * @param array $restrictedKeys
     */
    public function __construct(
        GetAllowedAddressAttributes $getAllowedAddressAttributes,
        AddressRepositoryInterface $addressRepository,
        DataObjectHelper $dataObjectHelper,
        DirectoryData $directoryData,
        RegionCollectionFactory $regionCollectionFactory,
        ValidateAddress $addressValidator,
        PopulateCustomerAddressFromInput $populateCustomerAddressFromInput,
        array $restrictedKeys = []
    ) {
        $this->getAllowedAddressAttributes = $getAllowedAddressAttributes;
        $this->addressRepository = $addressRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->directoryData = $directoryData;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->addressValidator = $addressValidator;
        $this->populateCustomerAddressFromInput = $populateCustomerAddressFromInput;
        $this->restrictedKeys = $restrictedKeys;
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
        $this->validateData($data);

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
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }

    /**
     * Validate customer address update data
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
                && (isset($addressData[$attributeName]) && empty($addressData[$attributeName]))
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
