<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Test\Fixture\CustomerWithAddresses;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verify that partial REST updates preserve addresses without rewriting unchanged rows.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateCustomerTest extends TestCase
{
    /**
     * @param bool $changeDefaults
     * @param int $userType
     * @param bool $hasCustomerIdParam
     * @return void
     * @magentoDbIsolation enabled
     */
    #[DataFixture(CustomerWithAddresses::class, as: 'customer')]
    #[DataProvider('defaultAddressDataProvider')]
    public function testAccountUpdateDoesNotSaveAddresses(
        bool $changeDefaults,
        int $userType,
        bool $hasCustomerIdParam
    ): void {
        $objectManager = Bootstrap::getObjectManager();
        $customerId = (int)DataFixtureStorageManager::getStorage()->get('customer')->getId();
        $connection = $objectManager->get(ResourceConnection::class)->getConnection('customer');
        $addressTable = $connection->getTableName('customer_address_entity');
        $connection->update(
            $addressTable,
            ['updated_at' => '2001-01-01 00:00:00'],
            ['parent_id = ?' => $customerId]
        );
        // Fixture saves populate snapshots; clear them to simulate a fresh REST request.
        $objectManager->get('EavVersionControlSnapshot')->clear();
        $objectManager->get('CustomerAddressSnapshot')->clear();
        $objectManager->get(CustomerRegistry::class)->remove($customerId);
        $customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $originalCustomer = $customerRepository->getById($customerId);
        $originalAddresses = $this->getAddressesDataById($originalCustomer->getAddresses());
        $this->assertCount(3, $originalAddresses);
        $defaultBilling = $originalCustomer->getDefaultBilling();
        $defaultShipping = $originalCustomer->getDefaultShipping();
        $incomingCustomer = $objectManager->get(CustomerInterfaceFactory::class)->create();
        $incomingCustomer->setId($customerId)
            ->setEmail($originalCustomer->getEmail())
            ->setFirstname('Updated')
            ->setLastname($originalCustomer->getLastname())
            ->setWebsiteId($originalCustomer->getWebsiteId())
            ->setDefaultBilling($defaultBilling)
            ->setDefaultShipping($defaultShipping);
        if ($changeDefaults) {
            $defaultBilling = (string)$originalCustomer->getAddresses()[1]->getId();
            $defaultShipping = (string)$originalCustomer->getAddresses()[2]->getId();
            $incomingCustomer->setDefaultBilling($defaultBilling)->setDefaultShipping($defaultShipping);
        }

        $request = $this->createStub(Request::class);
        $request->method('getParam')->willReturn($hasCustomerIdParam ? (string)$customerId : null);
        $request->method('getBodyParams')->willReturn([]);
        $userContext = $this->createStub(UserContextInterface::class);
        $userContext->method('getUserType')->willReturn($userType);
        $userContext->method('getUserId')
            ->willReturn($userType === UserContextInterface::USER_TYPE_CUSTOMER ? $customerId : 0);
        $plugin = new UpdateCustomer($request, $userContext);
        $profiler = $connection->getProfiler();
        $wasEnabled = $profiler->getEnabled();
        $profiler->clear();
        $profiler->setEnabled(true);
        try {
            [$customer] = $plugin->beforeSave($customerRepository, $incomingCustomer);
            $savedCustomer = $customerRepository->save($customer);
            $addressWrites = array_filter(
                $profiler->getQueryProfiles(
                    \Zend_Db_Profiler::INSERT | \Zend_Db_Profiler::UPDATE | \Zend_Db_Profiler::DELETE
                ) ?: [],
                static fn ($query): bool => str_contains($query->getQuery(), $addressTable)
            );
        } finally {
            $profiler->setEnabled($wasEnabled);
            $profiler->clear();
        }

        $this->assertCount(0, $addressWrites, 'Account-only updates must not write unchanged address rows.');
        $this->assertSame(
            ['2001-01-01 00:00:00'],
            array_values(array_unique($connection->fetchCol(
                $connection->select()->from($addressTable, 'updated_at')->where('parent_id = ?', $customerId)
            )))
        );
        $this->assertSame('Updated', $savedCustomer->getFirstname());
        $this->assertEquals($defaultBilling, $savedCustomer->getDefaultBilling());
        $this->assertEquals($defaultShipping, $savedCustomer->getDefaultShipping());
        $savedAddresses = $this->getAddressesDataById($savedCustomer->getAddresses());
        foreach (array_keys($originalAddresses) as $addressId) {
            $originalAddresses[$addressId]['default_billing'] = $addressId == $defaultBilling;
            $originalAddresses[$addressId]['default_shipping'] = $addressId == $defaultShipping;
        }
        $this->assertEquals($originalAddresses, $savedAddresses);
    }

    /**
     * @return array
     */
    public static function defaultAddressDataProvider(): array
    {
        $cases = [];
        foreach ([
            'admin' => [UserContextInterface::USER_TYPE_ADMIN, true],
            'integration' => [UserContextInterface::USER_TYPE_INTEGRATION, true],
            'customer by ID' => [UserContextInterface::USER_TYPE_CUSTOMER, true],
            'customer self-update' => [UserContextInterface::USER_TYPE_CUSTOMER, false],
        ] as $role => [$userType, $hasCustomerIdParam]) {
            $cases[$role . ' preserves defaults'] = [false, $userType, $hasCustomerIdParam];
            if ($hasCustomerIdParam) {
                $cases[$role . ' changes defaults'] = [true, $userType, $hasCustomerIdParam];
            }
        }

        return $cases;
    }

    /**
     * Compare addresses independently of default-address ordering and omitted false flags.
     *
     * @param AddressInterface[] $addresses
     * @return array
     */
    private function getAddressesDataById(array $addresses): array
    {
        $data = [];
        foreach ($addresses as $address) {
            $addressId = $address->getId();
            $data[$addressId] = $address->__toArray();
            $data[$addressId]['default_billing'] = (bool)$address->isDefaultBilling();
            $data[$addressId]['default_shipping'] = (bool)$address->isDefaultShipping();
        }

        return $data;
    }
}
