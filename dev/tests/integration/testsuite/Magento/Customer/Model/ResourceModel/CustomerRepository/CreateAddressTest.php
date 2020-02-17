<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\CustomerRepository;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\CreateAddressTest as CreateAddressViaAddressRepositoryTest;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Test cases related to create customer address using customer repository.
 *
 * @magentoDbIsolation enabled
 */
class CreateAddressTest extends CreateAddressViaAddressRepositoryTest
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dataObjectHelper = $this->objectManager->get(DataObjectHelper::class);
    }

    /**
     * Create customer address with provided address data.
     *
     * @param int $customerId
     * @param array $addressData
     * @param bool $isDefaultShipping
     * @param bool $isDefaultBilling
     * @return AddressInterface
     */
    protected function createAddress(
        int $customerId,
        array $addressData,
        bool $isDefaultShipping = false,
        bool $isDefaultBilling = false
    ): AddressInterface {
        if (isset($addressData['custom_region_name'])) {
            $addressData[AddressInterface::REGION_ID] = $this->getRegionIdByName->execute(
                $addressData['custom_region_name'],
                $addressData[AddressInterface::COUNTRY_ID]
            );
            unset($addressData['custom_region_name']);
        }
        $address = $this->addressFactory->create();
        $this->dataObjectHelper->populateWithArray($address, $addressData, AddressInterface::class);
        $address->setIsDefaultShipping($isDefaultShipping);
        $address->setIsDefaultBilling($isDefaultBilling);
        $customer = $this->customerRepository->getById($customerId);
        $customer->setAddresses([$address]);
        $this->customerRepository->save($customer);
        $addressId = (int)$address->getId();
        $this->customerRegistry->remove($customerId);

        return $this->addressRepository->getById($addressId);
    }
}
