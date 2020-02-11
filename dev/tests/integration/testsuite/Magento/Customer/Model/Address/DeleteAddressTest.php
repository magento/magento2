<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
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
    protected $customerRegistry;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
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
    public function testDeleteDefaultAddress(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertEquals(1, $customer->getDefaultShipping());
        $this->assertEquals(1, $customer->getDefaultBilling());
        $customerAddresses = $customer->getAddresses() ?? [];
        foreach ($customerAddresses as $address) {
            $this->addressRepository->delete($address);
        }
        $this->customerRegistry->remove($customer->getId());
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertNull($customer->getDefaultShipping());
        $this->assertNull($customer->getDefaultBilling());
    }

    /**
     * Assert that deleting non-existent address throws exception.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with addressId = 1
     *
     * @return void
     */
    public function testDeleteMissingAddress(): void
    {
        $this->addressRepository->deleteById(1);
    }
}
