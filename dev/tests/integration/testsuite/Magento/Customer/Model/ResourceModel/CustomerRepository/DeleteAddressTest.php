<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\CustomerRepository;

use Magento\Customer\Model\Address\DeleteAddressTest as DeleteAddressViaAddressRepositoryTest;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test cases related to delete customer address using customer repository.
 *
 * @magentoDbIsolation enabled
 */
class DeleteAddressTest extends DeleteAddressViaAddressRepositoryTest
{
    /**
     * Assert that address deleted successfully.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @return void
     */
    public function testDeleteDefaultAddress(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertEquals(1, $customer->getDefaultShipping());
        $this->assertEquals(1, $customer->getDefaultBilling());
        $customer->setAddresses([]);
        $this->customerRepository->save($customer);
        $this->customerRegistry->remove($customer->getId());
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertNull($customer->getDefaultShipping());
        $this->assertNull($customer->getDefaultBilling());
        $this->expectExceptionObject(new NoSuchEntityException(__('No such entity with addressId = 1')));
        $this->addressRepository->getById(1);
    }
}
