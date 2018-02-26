<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Helper\Delegation;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Helper\Delegation\Data\NewOperation;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Data\Address;
use Magento\Customer\Model\Session;
use Magento\Customer\Helper\Delegation\Data\NewOperationFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;

/**
 * Store data for delegated operations.
 */
class Storage
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var NewOperationFactory
     */
    private $newFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @param Session $session
     * @param NewOperationFactory $newFactory
     * @param CustomerInterfaceFactory $customerFactory
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionInterfaceFactory $regionFactory
     */
    public function __construct(
        Session $session,
        NewOperationFactory $newFactory,
        CustomerInterfaceFactory $customerFactory,
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory
    ) {
        $this->session = $session;
        $this->newFactory = $newFactory;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Store data for new account operation.
     *
     * @param CustomerInterface $customer
     * @param array $delegatedData
     *
     * @return void
     */
    public function storeNewOperation(
        CustomerInterface $customer,
        array $delegatedData
    ) {
        /** @var Customer $customer */
        $customerData = $customer->__toArray();
        $addressesData = [];
        if ($customer->getAddresses()) {
            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                $addressesData[] = $address->__toArray();
            }
        }
        $this->session->setCustomerFormData($customerData);
        $this->session->setDelegatedNewCustomerData([
            'customer' => $customerData,
            'addresses' => $addressesData,
            'delegated_data' => $delegatedData
        ]);
    }

    /**
     * Retrieve delegated new operation data and mark it as used.
     *
     * @return NewOperation|null
     */
    public function consumeNewOperation()
    {
        $serialized = $this->session->getDelegatedNewCustomerData(true);
        if (!$serialized) {
            return null;
        }

        /** @var AddressInterface[] $addresses */
        $addresses = [];
        foreach ($serialized['addresses'] as $addressData) {
            if (isset($addressData['region'])) {
                /** @var RegionInterface $region */
                $region = $this->regionFactory->create(
                    ['data' => $addressData['region']]
                );
                $addressData['region'] = $region;
            }
            $addresses[] = $this->addressFactory->create(
                ['data' => $addressData]
            );
        }
        $customerData = $serialized['customer'];
        $customerData['addresses'] = $addresses;

        return $this->newFactory->create([
            'customer' => $this->customerFactory->create(
                ['data' => $customerData]
            ),
            'additionalData' => $serialized['delegated_data']
        ]);
    }
}
