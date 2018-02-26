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
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param NewOperationFactory $newFactory
     * @param CustomerInterfaceFactory $customerFactory
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionInterfaceFactory $regionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        NewOperationFactory $newFactory,
        CustomerInterfaceFactory $customerFactory,
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory,
        LoggerInterface $logger
    ) {
        $this->newFactory = $newFactory;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->logger = $logger;
    }

    /**
     * @return Session
     *
     * @throws \Throwable
     */
    private function getSession(): Session
    {
        if (!$this->session) {
            $this->session = ObjectManager::getInstance()->get(Session::class);
        }

        return $this->session;
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
        $this->getSession()->setCustomerFormData($customerData);
        $this->getSession()->setDelegatedNewCustomerData([
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
        try {
            $serialized = $this->getSession()
                ->getDelegatedNewCustomerData(true);
        } catch (\Throwable $exception) {
            $this->logger->error($exception);
            $serialized = null;
        }
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
