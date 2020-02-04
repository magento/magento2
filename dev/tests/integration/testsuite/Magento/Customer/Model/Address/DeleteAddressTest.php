<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Assert that address was deleted successfully.
 *
 * @magentoDbIsolation enabled
 */
class DeleteAddressTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->addressRegistry = $this->objectManager->get(AddressRegistry::class);
        $this->addressResource = $this->objectManager->get(Address::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        parent::setUp();
    }

    /**
     * Assert that address deleted successfully.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @return void
     */
    public function testAddressDelete(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $address = $this->addressRepository->getById(1);
        $this->assertEquals(1, $customer->getDefaultShipping());
        $this->assertEquals(1, $customer->getDefaultBilling());
        $this->addressRepository->delete($address);
        $this->customerRegistry->remove(1);
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertNull($customer->getDefaultShipping());
        $this->assertNull($customer->getDefaultBilling());
        $this->expectExceptionObject(new NoSuchEntityException(__('No such entity with addressId = 1')));
        $this->addressRepository->getById(1);
    }
}
