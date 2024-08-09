<?php
/*************************************************************************
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Test\Unit\Model\Import\Customer;

use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\Storage;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class StorageTest extends TestCase
{
    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * @var Collection|MockObject
     */
    private mixed $customerCollectionMock;

    /**
     * @var Share|MockObject
     */
    private mixed $configShareMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private mixed $connectionMock;

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
     * Test loadCustomersData method when the scope is set to global.
     *
     * @throws Exception|ReflectionException
     */
    public function testLoadCustomersData()
    {
        $customerIdentifiers = [
            'test@example.com_2' => ['email' => 'test@example.com', 'website_id' => 2],
            'test@example.com_3' => ['email' => 'test@example.com', 'website_id' => 3],
            'test@example.com_4' => ['email' => 'test@example.com', 'website_id' => 4],
            'test@example.com_5' => ['email' => 'test@example.com', 'website_id' => 5]
        ];

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())
            ->method('getPart')
            ->willReturn(['main_table' => 'customer_entity']);

        $this->customerCollectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($selectMock);
        $connectionMock = $this->getConnectionMock();
        $this->customerCollectionMock
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock);

        $this->configShareMock->expects($this->once())
            ->method('isGlobalScope')
            ->willReturn(true);

        $reflection = new ReflectionClass($this->storage);
        $customerIdsProperty = $reflection->getProperty('_customerIds');
        $loadCustomersDataMethod = $reflection->getMethod('loadCustomersData');
        $loadCustomersDataMethod->setAccessible(true);
        $loadCustomersDataMethod->invokeArgs($this->storage, [$customerIdentifiers]);
        $customerIds = $customerIdsProperty->getValue($this->storage);
        $this->assertArrayHasKey('test@example.com', $customerIds);
    }

    /**
     * Mock DB connection and return customer data's
     *
     * @return AdapterInterface
     * @throws Exception
     */
    private function getConnectionMock(): AdapterInterface
    {
        $customerData = [
            'email' => 'test@example.com',
            'website_id' => 1,
            'entity_id' => 1,
            'store_id' => 1
        ];
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')->willReturn([$customerData]);

        return $this->connectionMock;
    }
}
