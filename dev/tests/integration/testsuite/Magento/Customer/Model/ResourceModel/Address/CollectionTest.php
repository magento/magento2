<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Tests for customer addresses collection
 */
namespace Magento\Customer\Model\ResourceModel\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Test\Fixture\CustomerWithAddresses;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testSetCustomerFilter()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Address\Collection::class
        );
        $select = $collection->getSelect();
        $this->assertSame($collection, $collection->setCustomerFilter([1, 2]));
        $customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Customer::class
        );
        $collection->setCustomerFilter($customer);
        $customer->setId(3);
        $collection->setCustomerFilter($customer);
        $this->assertStringMatchesFormat(
            '%AWHERE%S(%Sparent_id%S IN(%S1%S, %S2%S))%SAND%S(%Sparent_id%S = %S-1%S)%SAND%S(%Sparent_id%S = %S3%S)%A',
            (string)$select
        );
    }

    #[
        DbIsolation(true),
        DataFixture(CustomerWithAddresses::class, as: 'customer'),
        DataProvider('addressChangeDataProvider')
    ]
    public function testLoadedAddressChangesArePersisted(bool $changeDefault): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $customerId = (int)DataFixtureStorageManager::getStorage()->get('customer')->getId();
        $objectManager->get('CustomerAddressSnapshot')->clear();
        $customerRegistry = $objectManager->get(CustomerRegistry::class);
        $customerRegistry->remove($customerId);
        $customer = $customerRegistry->retrieve($customerId);
        $addresses = array_values($customer->getAddresses());
        $address = $addresses[1];
        $addressId = (int)$address->getId();

        if ($changeDefault) {
            $address->setIsDefaultBilling(true)->save();
        } else {
            $address->setCity('Updated city');
            $customer->save();
        }

        $connection = $objectManager->get(ResourceConnection::class)->getConnection('customer');
        $city = $connection->fetchOne(
            $connection->select()->from($connection->getTableName('customer_address_entity'), 'city')
                ->where('entity_id = ?', $addressId)
        );
        $this->assertSame($changeDefault ? 'Los Angeles' : 'Updated city', $city);
        $customerRegistry->remove($customerId);
        $savedCustomer = $objectManager->get(CustomerRepositoryInterface::class)->getById($customerId);
        $this->assertEquals(
            $changeDefault ? $addressId : $addresses[0]->getId(),
            $savedCustomer->getDefaultBilling()
        );
        $this->assertCount(3, $savedCustomer->getAddresses());
    }

    public static function addressChangeDataProvider(): array
    {
        return [
            'save edited collection address through customer relation' => [false],
            'save default billing flag without changing address fields' => [true],
        ];
    }
}
