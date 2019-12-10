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
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

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
     * @var DirectoryData
     */
    private $directoryData;

    /**
     * @param GetAllowedAddressAttributes $getAllowedAddressAttributes
     * @param AddressInterfaceFactory $addressFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param DirectoryData $directoryData
     */
    public function __construct(
        GetAllowedAddressAttributes $getAllowedAddressAttributes,
        AddressInterfaceFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        DataObjectHelper $dataObjectHelper,
        DirectoryData $directoryData
    ) {
        $this->getAllowedAddressAttributes = $getAllowedAddressAttributes;
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->directoryData = $directoryData;
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

        //Add error for empty postcode with country with no optional ZIP
        if (!$this->directoryData->isZipCodeOptional($addressData['country_id'])
            && (!isset($addressData['postcode']) || empty($addressData['postcode']))
        ) {
            $errorInput[] = 'postcode';
        }

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
