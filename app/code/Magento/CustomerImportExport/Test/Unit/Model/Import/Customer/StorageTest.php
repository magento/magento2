<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Test\Unit\Model\Import\Customer;

use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    /**
     * @var MockObject|Storage
     */
    private MockObject|Storage $storage;

    /**
     * @var MockObject|Collection
     */
    private MockObject|Collection $customerCollectionMock;

    /**
     * @var MockObject|Share
     */
    private MockObject|Share $configShareMock;

    /**
     * @var MockObject|AdapterInterface
     */
    private MockObject|AdapterInterface $connectionMock;

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function setUp(): void
    {
        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->customerCollectionMock = $this->createMock(Collection::class);
        $this->configShareMock = $this->createMock(Share::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);

        $collectionFactoryMock->method('create')
            ->willReturn($this->customerCollectionMock);

        $this->storage = new Storage(
            $collectionFactoryMock,
            $this->configShareMock,
            ['page_size' => 4]
        );
    }

    /**
     * Test prepareCustomers when the scope is set to global.
     *
     * @throws Exception
     */
    #[DataProvider('customerDataProvider')]
    public function testPrepareCustomers(array $customersToFind, array $customersData, array $expectedResults): void
    {
        $this->mockCustomerCollection($customersData);
        $this->storage->prepareCustomers($customersToFind);

        foreach ($expectedResults as $email => $expectedResult) {
            foreach ($expectedResult as $websiteId => $expectedCustomerId) {
                $this->assertEquals($expectedCustomerId, $this->storage->getCustomerId($email, $websiteId));
            }
        }
    }

    /**
     * Data provider for testPrepareCustomers.
     *
     * @return array[]
     */
    public static function customerDataProvider(): array
    {
        return [
            'Test sample customers data' => [
                'customersToFind' => [
                    ['email' => 'test@example.com', 'website_id' => 3],
                    ['email' => 'test@example.com', 'website_id' => 4],
                    ['email' => 'test@example.com', 'website_id' => 5],
                    ['email' => 'test@example.com', 'website_id' => 6],
                ],
                'customersData' => [
                    ['email' => 'test@example.com', 'website_id' => 1, 'entity_id' => 1, 'store_id' => 1],
                    ['email' => 'test@example.com', 'website_id' => 2, 'entity_id' => 2, 'store_id' => 2],
                ],
                'expectedResults' => [
                    'test@example.com' => [
                        1 => 1,
                        2 => 2,
                    ],
                ]
            ],
        ];
    }

    /**
     * Test prepareCustomers with uppercase email in Global scope.
     *
     * @throws Exception
     */
    public function testPrepareCustomersWithUppercaseEmailGlobalScope(): void
    {
        $customersToFind = [
            ['email' => 'Uppercase.Email@example.com', 'website_id' => 1],
        ];
        $customersData = [
            ['email' => 'Uppercase.Email@example.com', 'website_id' => 1, 'entity_id' => 100, 'store_id' => 1],
        ];

        $this->mockCustomerCollectionForGlobalScope($customersData);
        $this->storage->prepareCustomers($customersToFind);

        $this->assertEquals(100, $this->storage->getCustomerId('uppercase.email@example.com', 1));
        $this->assertEquals(100, $this->storage->getCustomerId('Uppercase.Email@example.com', 1));
    }

    /**
     * Test prepareCustomers with uppercase email in Website scope.
     *
     * @throws Exception
     */
    public function testPrepareCustomersWithUppercaseEmailWebsiteScope(): void
    {
        $customersToFind = [
            ['email' => 'Uppercase.Email@example.com', 'website_id' => 1],
        ];
        $customersData = [
            ['email' => 'Uppercase.Email@example.com', 'website_id' => 1, 'entity_id' => 100, 'store_id' => 1],
        ];

        $this->mockCustomerCollectionForWebsiteScope($customersData);
        $this->storage->prepareCustomers($customersToFind);

        $this->assertEquals(100, $this->storage->getCustomerId('uppercase.email@example.com', 1));
    }

    /**
     * Test prepareCustomers with mixed case emails in Global scope.
     *
     * @throws Exception
     */
    public function testPrepareCustomersWithMixedCaseEmailsGlobalScope(): void
    {
        $customersToFind = [
            ['email' => 'Mixed.Case@Example.COM', 'website_id' => 1],
            ['email' => 'mixed.case@example.com', 'website_id' => 2],
        ];
        $customersData = [
            ['email' => 'Mixed.Case@Example.COM', 'website_id' => 1, 'entity_id' => 200, 'store_id' => 1],
            ['email' => 'mixed.case@example.com', 'website_id' => 2, 'entity_id' => 201, 'store_id' => 2],
        ];

        $this->mockCustomerCollectionForGlobalScope($customersData);
        $this->storage->prepareCustomers($customersToFind);

        $this->assertEquals(200, $this->storage->getCustomerId('mixed.case@example.com', 1));
        $this->assertEquals(201, $this->storage->getCustomerId('mixed.case@example.com', 2));
    }

    /**
     * Test email normalization consistency when building customer websites map.
     *
     * @throws Exception
     */
    public function testEmailNormalizationConsistency(): void
    {
        $customersToFind = [
            ['email' => 'UPPERCASE@EXAMPLE.COM', 'website_id' => 1],
        ];
        $customersData = [
            ['email' => 'uppercase@example.com', 'website_id' => 1, 'entity_id' => 300, 'store_id' => 1],
        ];

        $this->mockCustomerCollectionForGlobalScope($customersData);
        $this->storage->prepareCustomers($customersToFind);

        $this->assertEquals(300, $this->storage->getCustomerId('UPPERCASE@EXAMPLE.COM', 1));
        $this->assertEquals(300, $this->storage->getCustomerId('uppercase@example.com', 1));
    }

    /**
     * Mock the customer collection to return specific data.
     *
     * @param array $customersData
     * @throws Exception
     */
    private function mockCustomerCollection(array $customersData): void
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())
            ->method('getPart')
            ->willReturn(['main_table' => 'customer_entity']);

        $this->customerCollectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($selectMock);

        $this->customerCollectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->mockConnection($customersData));

        $this->configShareMock->expects($this->exactly(2))
            ->method('isGlobalScope')
            ->willReturn(true);
    }

    /**
     * Mock the customer collection for Global scope tests.
     *
     * @param array $customersData
     * @throws Exception
     */
    private function mockCustomerCollectionForGlobalScope(array $customersData): void
    {
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPart', 'where'])
            ->getMock();
        
        $selectMock->expects($this->atLeastOnce())
            ->method('getPart')
            ->willReturn(['main_table' => 'customer_entity']);
        
        $selectMock->method('where')
            ->willReturnSelf();

        $this->customerCollectionMock->expects($this->atLeastOnce())
            ->method('removeAttributeToSelect')
            ->willReturnSelf();

        $this->customerCollectionMock->expects($this->atLeastOnce())
            ->method('getSelect')
            ->willReturn($selectMock);

        $this->customerCollectionMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->mockConnection($customersData));

        $this->configShareMock->expects($this->atLeastOnce())
            ->method('isGlobalScope')
            ->willReturn(true);
    }

    /**
     * Mock the customer collection for Website scope tests.
     *
     * @param array $customersData
     * @throws Exception
     */
    private function mockCustomerCollectionForWebsiteScope(array $customersData): void
    {
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPart', 'where'])
            ->getMock();
        
        $selectMock->expects($this->atLeastOnce())
            ->method('getPart')
            ->willReturn(['main_table' => 'customer_entity']);
        
        $selectMock->method('where')
            ->willReturnSelf();

        $this->customerCollectionMock->expects($this->atLeastOnce())
            ->method('removeAttributeToSelect')
            ->willReturnSelf();

        $this->customerCollectionMock->expects($this->atLeastOnce())
            ->method('getSelect')
            ->willReturn($selectMock);

        $this->customerCollectionMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->mockConnection($customersData));

        $this->configShareMock->expects($this->atLeastOnce())
            ->method('isGlobalScope')
            ->willReturn(false);
    }

    /**
     * Mock the database connection to return specific customer data.
     *
     * @param array $customersData
     * @return MockObject
     * @throws Exception
     */
    private function mockConnection(array $customersData): MockObject
    {
        $connectionMock = $this->createMock(AdapterInterface::class);
        $connectionMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($customersData);

        return $connectionMock;
    }
}
