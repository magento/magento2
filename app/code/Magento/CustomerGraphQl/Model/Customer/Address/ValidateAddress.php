<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Customer address validation used during customer account creation and updating
 */
class ValidateAddress
{
    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @var DirectoryData
     */
    private $directoryData;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @var ExtractCustomerAddressData
     */
    private $extractCustomerAddressData;

    /**
     * ValidateCustomerData constructor.
     *
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionInterfaceFactory $regionFactory
     * @param DirectoryData $directoryData
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     */
    public function __construct(
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory,
        DirectoryData $directoryData,
        RegionCollectionFactory $regionCollectionFactory,
        ExtractCustomerAddressData $extractCustomerAddressData
    ) {
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->directoryData = $directoryData;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->extractCustomerAddressData = $extractCustomerAddressData;
    }

    /**
     * Validate customer address data
     *
     * @param AddressInterface $address
     * @throws GraphQlInputException
     */
    public function execute(AddressInterface $address): void
    {
        $addressData = $this->extractCustomerAddressData->execute($address);

        if (isset($addressData['country_code'])) {
            $isRegionRequired = $this->directoryData->isRegionRequired($addressData['country_code']);

            if ($isRegionRequired && empty($addressData['region']['region_id'])) {
                throw new GraphQlInputException(__('A region_id is required for the specified country code'));
            }
            $regionCollection = $this->regionCollectionFactory
                ->create()
                ->addCountryFilter($addressData['country_code']);

            if ($isRegionRequired) {
                if (!empty($addressData['region']['region_code'])) {
                    $regionCollection->addRegionCodeFilter($addressData['region']['region_code']);
                }

                if (empty($regionCollection->getItemById($addressData['region']['region_id']))) {
                    throw new GraphQlInputException(
                        __('The specified region is not a part of the selected country or region')
                    );
                }
            } else {
                if (!empty($addressData['region']['region_id']) &&
                    empty($regionCollection->getItemById($addressData['region']['region_id']))) {
                    throw new GraphQlInputException(
                        __('The region_id does not match the selected country or region')
                    );
                }
            }
        }
    }
}
