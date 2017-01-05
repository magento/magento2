<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Controller\Cron;

class BulkCleanupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\AsynchronousOperations\Cron\BulkCleanup
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $timeMock;

    protected function setUp()
    {
        $this->dateTimeMock = $this->getMock(\Magento\Framework\Stdlib\DateTime::class, [], [], '', false);
        $this->scopeConfigMock = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            [],
            '',
            false
        );
        $this->resourceConnectionMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );
        $this->metadataPoolMock = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );
        $this->timeMock = $this->getMock(\Magento\Framework\Stdlib\DateTime\DateTime::class, [], [], '', false);
        $this->model = new \Magento\AsynchronousOperations\Cron\BulkCleanup(
            $this->metadataPoolMock,
            $this->resourceConnectionMock,
            $this->dateTimeMock,
            $this->scopeConfigMock,
            $this->timeMock
        );
    }

    public function testExecute()
    {
        $entityType = 'BulkSummaryInterface';
        $connectionName = 'Connection';
        $bulkLifetimeMultiplier = 10;
        $bulkLifetime = 3600 * 24 * $bulkLifetimeMultiplier;

        $adapterMock = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class, [], [], '', false);
        $entityMetadataMock = $this->getMock(
            \Magento\Framework\EntityManager\EntityMetadataInterface::class,
            [],
            [],
            '',
            false
        );

        $this->metadataPoolMock->expects($this->once())->method('getMetadata')->with($this->stringContains($entityType))
            ->willReturn($entityMetadataMock);
        $entityMetadataMock->expects($this->once())->method('getEntityConnectionName')->willReturn($connectionName);
        $this->resourceConnectionMock->expects($this->once())->method('getConnectionByName')->with($connectionName)
            ->willReturn($adapterMock);
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with($this->stringContains('bulk/lifetime'))
            ->willReturn($bulkLifetimeMultiplier);
        $this->timeMock->expects($this->once())->method('gmtTimestamp')->willReturn($bulkLifetime*10);
        $this->dateTimeMock->expects($this->once())->method('formatDate')->with($bulkLifetime*9);
        $adapterMock->expects($this->once())->method('delete');

        $this->model->execute();
    }
}
