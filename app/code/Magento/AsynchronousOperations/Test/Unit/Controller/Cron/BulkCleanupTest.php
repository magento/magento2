<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Controller\Cron;

use Magento\AsynchronousOperations\Cron\BulkCleanup;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Stdlib\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BulkCleanupTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $metadataPoolMock;

    /**
     * @var MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var MockObject
     */
    private $dateTimeMock;

    /**
     * @var MockObject
     */
    private $scopeConfigMock;

    /**
     * @var BulkCleanup
     */
    private $model;

    /**
     * @var MockObject
     */
    private $timeMock;

    protected function setUp(): void
    {
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->timeMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $this->model = new BulkCleanup(
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

        $adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $entityMetadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);

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
