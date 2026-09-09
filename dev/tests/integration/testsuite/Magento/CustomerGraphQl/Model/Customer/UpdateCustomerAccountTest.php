<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Test\Fixture\CustomerWithAddresses;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration coverage for the customer account update flow used by updateCustomerV2.
 */
#[
    DataFixture(CustomerWithAddresses::class, [], 'customer')
]
class UpdateCustomerAccountTest extends TestCase
{
    /**
     * Updating an account-only field must not re-save the customer's addresses.
     *
     * The mutation input cannot carry addresses, so the addresses are detached before the customer is saved.
     * The address repository must therefore never be asked to save an address, no matter how large the address
     * book is. This is the behaviour that keeps the mutation's response time constant instead of scaling with
     * the number of saved addresses.
     */
    public function testUpdateDoesNotResaveAddresses(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $customerId = (int)$customer->getId();

        // Fail the moment any address is pushed through the address repository during an account-only update.
        $addressRepository = $this->createMock(AddressRepository::class);
        $addressRepository->expects($this->never())->method('save');

        $customerRepository = $objectManager->create(
            CustomerRepository::class,
            ['addressRepository' => $addressRepository]
        );
        $saveCustomer = $objectManager->create(
            SaveCustomer::class,
            ['customerRepository' => $customerRepository]
        );
        $updateCustomerAccount = $objectManager->create(
            UpdateCustomerAccount::class,
            ['saveCustomer' => $saveCustomer]
        );

        $store = $objectManager->get(StoreManagerInterface::class)->getStore();

        // Load the customer with its address collection populated, exactly as the resolver's GetCustomer does.
        $realRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $loaded = $realRepository->getById($customerId);

        $updateCustomerAccount->execute($loaded, ['lastname' => 'Rowe'], $store);

        // The account field is updated and the address book is left fully intact.
        $persisted = $realRepository->getById($customerId);
        $this->assertSame('Rowe', $persisted->getLastname());
        $this->assertCount(3, $persisted->getAddresses());
    }
}
