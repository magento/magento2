<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Populate customer address from the input
 */
class PopulateCustomerAddressFromInput
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
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionInterfaceFactory $regionFactory
     * @param DirectoryData $directoryData
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory,
        DirectoryData $directoryData,
        RegionCollectionFactory $regionCollectionFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->directoryData = $directoryData;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Populate the customer address and region data from input
     *
     * @param AddressInterface $address
     * @param array $addressData
     * @return AddressInterface
     * @throws GraphQlInputException
     */
    public function execute(AddressInterface $address, array $addressData): AddressInterface
    {
        $this->dataObjectHelper->populateWithArray($address, $addressData, AddressInterface::class);

        return $this->setRegionData($address, $addressData);
    }

    /**
     * Set the address region data
     *
     * @param AddressInterface $address
     * @param array $addressData
     * @return AddressInterface
     * @throws GraphQlInputException
     */
    private function setRegionData(AddressInterface $address, array $addressData):AddressInterface
    {
        if (!empty($addressData['region']['region_id'])) {
            if (array_key_exists('country_code', $addressData)) {
                $regionCollection = $this->regionCollectionFactory
                   ->create()
                   ->addCountryFilter($addressData['country_code']);
                if (!empty($addressData['region']['region_code'])) {
                    $regionCollection->addRegionCodeFilter($addressData['region']['region_code']);
                }

                $regionResult = $regionCollection->getItemById($addressData['region']['region_id']);
                /** @var RegionInterface $region */
                $region = $this->regionFactory->create();
                if ($regionResult != null) {
                    $region->setRegionId($regionResult->getRegionId());
                    $region->setRegionCode($regionResult->getCode());
                    $region->setRegion($regionResult->getDefaultName());
                    $address->setRegion($region);
                } else {
                    throw new GraphQlInputException(
                        __('The region_id does not match the selected country or region')
                    );
                }
            }
        }
        return $address;
    }
}
