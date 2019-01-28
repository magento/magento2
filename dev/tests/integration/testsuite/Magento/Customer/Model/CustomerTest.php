<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

class CustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerFactory;

    protected function setUp()
    {
        $this->customerModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Customer::class
        );
        $this->customerFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class
        );
    }

    public function testUpdateDataSetDataOnEmptyModel()
    {
        /** @var \Magento\Customer\Model\Data\Customer $customerData */
        $customerData = $this->customerFactory->create()
            ->setId(1)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setDefaultBilling(1);
        $customerData = $this->customerModel->updateData($customerData)->getDataModel();

        $this->assertSame(1, $customerData->getId());
        $this->assertSame('John', $customerData->getFirstname());
        $this->assertSame('Doe', $customerData->getLastname());
        $this->assertSame(1, $customerData->getDefaultBilling());
    }

    public function testUpdateDataOverrideExistingData()
    {
        /** @var \Magento\Customer\Model\Data\Customer $customerData */
        $customerData = $this->customerFactory->create()
            ->setId(2)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setDefaultBilling(1);
        $this->customerModel->updateData($customerData);

        /** @var \Magento\Customer\Model\Data\Customer $updatedCustomerData */
        $updatedCustomerData = $this->customerFactory->create()
            ->setId(3)
            ->setFirstname('Jane')
            ->setLastname('Smith')
            ->setDefaultBilling(0);
        $updatedCustomerData = $this->customerModel->updateData($updatedCustomerData)->getDataModel();

        $this->assertSame(3, $updatedCustomerData->getId());
        $this->assertSame('Jane', $updatedCustomerData->getFirstname());
        $this->assertSame('Smith', $updatedCustomerData->getLastname());
        $this->assertSame(0, $updatedCustomerData->getDefaultBilling());
    }
}
