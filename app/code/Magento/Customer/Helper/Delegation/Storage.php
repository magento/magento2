<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Helper\Delegation;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Delegation\Data\NewOperation;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Data\Address;
use Magento\Customer\Model\Session;
use Magento\Customer\Helper\Delegation\Data\NewOperationFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;

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
     * @param Session $session
     * @param NewOperationFactory $newFactory
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        Session $session,
        NewOperationFactory $newFactory,
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->session = $session;
        $this->newFactory = $newFactory;
        $this->customerFactory = $customerFactory;
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
            'customer' => array_merge(
                $customerData,
                ['addresses' => $addressesData]
            ),
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
        $serialized = $this->session->gettDelegatedNewCustomerData(true);
        if (!$serialized) {
            return null;
        }

        return $this->newFactory->create([
            'customer' => $this->customerFactory->create(
                ['data' => $serialized['customer']]
            ),
            'additionalData' => $serialized['delegated_data']
        ]);
    }
}
