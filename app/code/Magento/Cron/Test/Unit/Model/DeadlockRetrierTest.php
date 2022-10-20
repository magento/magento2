<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Model;

use Magento\Cron\Model\DeadlockRetrier;
use Magento\Cron\Model\DeadlockRetrierInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DeadlockException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeadlockRetrierTest extends TestCase
{

    /**
     * @var DeadlockRetrier
     */
    private $retrier;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var AbstractModel|MockObject
     */
    private $modelMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->modelMock = $this->createMock(AbstractModel::class);
        $this->retrier = new DeadlockRetrier($this->loggerMock);
    }

    /**
     * @return void
     */
    public function testInsideTransaction(): void
    {
        $this->expectException(DeadlockException::class);

        $this->adapterMock->expects($this->once())
            ->method('getTransactionLevel')
            ->willReturn(1);
        $this->modelMock->expects($this->once())
            ->method('getId')
            ->willThrowException(new DeadlockException());

        $this->retrier->execute(
            function () {
                return $this->modelMock->getId();
            },
            $this->adapterMock
        );
    }

    /**
     * @return void
     */
    public function testRetry(): void
    {
        $this->expectException(DeadlockException::class);

        $this->adapterMock->expects($this->once())
            ->method('getTransactionLevel')
            ->willReturn(0);
        $this->modelMock->expects($this->exactly(DeadlockRetrierInterface::MAX_RETRIES))
            ->method('getId')
            ->willThrowException(new DeadlockException());
        $this->loggerMock->expects($this->exactly(DeadlockRetrierInterface::MAX_RETRIES - 1))
            ->method('warning');

        $this->retrier->execute(
            function () {
                return $this->modelMock->getId();
            },
            $this->adapterMock
        );
    }

    /**
     * @return void
     */
    public function testRetrySecond(): void
    {
        $this->adapterMock->expects($this->once())
            ->method('getTransactionLevel')
            ->willReturn(0);

        $this->modelMock
            ->method('getId')
            ->willReturnOnConsecutiveCalls($this->throwException(new DeadlockException()), 2);

        $this->retrier->execute(
            function () {
                return $this->modelMock->getId();
            },
            $this->adapterMock
        );
    }
}
