<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Observer;

use Magento\Cron\Observer\ProcessCronQueueObserver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Targeted regression for #40106: confirms ProcessCronQueueObserver rolls
 * back leftover transactions on the default connection between cron jobs.
 */
class ResetLeftoverTransactionTest extends TestCase
{
    public function testRollsBackOpenTransactionLeftByPreviousJob()
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('getTransactionLevel')
            ->willReturnOnConsecutiveCalls(1, 0);
        $connection->expects($this->once())->method('rollBack');

        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);

        $observer = $this->buildObserver($resource);
        $this->invokeReset($observer, 'job_code');
    }

    public function testNoActionWhenConnectionIsClean()
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('getTransactionLevel')->willReturn(0);
        $connection->expects($this->never())->method('rollBack');

        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);

        $observer = $this->buildObserver($resource);
        $this->invokeReset($observer, 'job_code');
    }

    public function testHandlesNestedTransactions()
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('getTransactionLevel')
            ->willReturnOnConsecutiveCalls(2, 1, 0);
        $connection->expects($this->exactly(2))->method('rollBack');

        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);

        $observer = $this->buildObserver($resource);
        $this->invokeReset($observer, 'job_code');
    }

    public function testBreaksOutWhenRollbackThrows()
    {
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('getTransactionLevel')->willReturn(1);
        $connection->method('rollBack')->willThrowException(new \RuntimeException('boom'));

        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $observer = $this->buildObserver($resource, $logger);
        $this->invokeReset($observer, 'job_code');
    }

    private function buildObserver(
        ResourceConnection $resource,
        ?LoggerInterface $logger = null
    ): ProcessCronQueueObserver {
        $om = new ObjectManager($this);
        $constructorArgs = [
            'logger' => $logger ?: $this->createMock(LoggerInterface::class),
            'resourceConnection' => $resource,
        ];
        return $om->getObject(ProcessCronQueueObserver::class, $constructorArgs);
    }

    private function invokeReset(ProcessCronQueueObserver $observer, string $jobCode): void
    {
        $method = new \ReflectionMethod($observer, 'resetLeftoverTransaction');
        $method->setAccessible(true);
        $method->invoke($observer, $jobCode);
    }
}
