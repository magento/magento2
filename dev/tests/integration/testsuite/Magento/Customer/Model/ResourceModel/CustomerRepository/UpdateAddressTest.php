<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\CustomerRepository;

use Magento\Customer\Model\Address\UpdateAddressTest as UpdateAddressViaAddressRepositoryTest;

/**
 * Test cases related to update customer address using customer repository.
 *
 * @magentoDbIsolation enabled
 */
class UpdateAddressTest extends UpdateAddressViaAddressRepositoryTest
{
    /**
     * Assert that default addresses properly updated for customer.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @dataProvider updateAddressIsDefaultDataProvider
     *
     * @param bool $isShippingDefault
     * @param bool $isBillingDefault
     * @param int|null $expectedShipping
     * @param int|null $expectedBilling
     * @return void
     */
    public function testUpdateAddressIsDefault(
        bool $isShippingDefault,
        bool $isBillingDefault,
        ?int $expectedShipping,
        ?int $expectedBilling
    ): void {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertEquals(1, $customer->getDefaultShipping());
        $this->assertEquals(1, $customer->getDefaultBilling());
        $this->processedAddressesIds[] = 1;
        $address = $this->addressRepository->getById(1);
        $address->setIsDefaultShipping($isShippingDefault);
        $address->setIsDefaultBilling($isBillingDefault);
        $customer->setAddresses([$address]);
        $this->customerRepository->save($customer);
        $this->customerRegistry->remove(1);
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertEquals($customer->getDefaultShipping(), $expectedShipping);
        $this->assertEquals($customer->getDefaultBilling(), $expectedBilling);
    }

    /**
     * Assert that address updated successfully.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @dataProvider updateAddressesDataProvider
     *
     * @param array $updateData
     * @param array $expectedData
     * @return void
     */
    public function testUpdateAddress(array $updateData, array $expectedData): void
    {
        $this->processedAddressesIds[] = 1;
        $address = $this->addressRepository->getById(1);
        foreach ($updateData as $setFieldName => $setValue) {
            $address->setData($setFieldName, $setValue);
        }
        $customer = $this->customerRepository->get('customer@example.com');
        $customer->setAddresses([$address]);
        $this->customerRepository->save($customer);
        $updatedAddressData = $this->addressRepository->getById((int)$address->getId())->__toArray();
        foreach ($expectedData as $getFieldName => $getValue) {
            $this->assertTrue(isset($updatedAddressData[$getFieldName]), "Field $getFieldName wasn't found.");
            $this->assertEquals($getValue, $updatedAddressData[$getFieldName]);
        }
    }

    /**
     * Assert that error message has thrown during process address update.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @dataProvider updateWrongAddressesDataProvider
     *
     * @param array $updateData
     * @param \Exception $expectException
     * @return void
     */
    public function testExceptionThrownDuringUpdateAddress(array $updateData, \Exception $expectException): void
    {
        $this->processedAddressesIds[] = 1;
        $address = $this->addressRepository->getById(1);
        $customer = $this->customerRepository->get('customer@example.com');
        foreach ($updateData as $setFieldName => $setValue) {
            $address->setData($setFieldName, $setValue);
        }
        $customer->setAddresses([$address]);
        $this->expectExceptionObject($expectException);
        $this->customerRepository->save($customer);
    }
}
