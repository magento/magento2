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

class DeadlockRetrierTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var DeadlockRetrier
     */
    private $retrier;

    /**
     * @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $adapterMock;

    /**
     * @var AbstractModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $modelMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->modelMock = $this->createMock(AbstractModel::class);
        $this->retrier = new DeadlockRetrier();
    }

    /**
     */
    public function testInsideTransaction(): void
    {
        $this->expectException(\Magento\Framework\DB\Adapter\DeadlockException::class);

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
     */
    public function testRetry(): void
    {
        $this->expectException(\Magento\Framework\DB\Adapter\DeadlockException::class);

        $this->adapterMock->expects($this->once())
            ->method('getTransactionLevel')
            ->willReturn(0);
        $this->modelMock->expects($this->exactly(DeadlockRetrierInterface::MAX_RETRIES))
            ->method('getId')
            ->willThrowException(new DeadlockException());

        $this->retrier->execute(
            function () {
                return $this->modelMock->getId();
            },
            $this->adapterMock
        );
    }

    public function testRetrySecond(): void
    {
        $this->adapterMock->expects($this->once())
            ->method('getTransactionLevel')
            ->willReturn(0);
        $this->modelMock->expects($this->at(0))
            ->method('getId')
            ->willThrowException(new DeadlockException());
        $this->modelMock->expects($this->at(1))
            ->method('getId')
            ->willReturn(2);

        $this->retrier->execute(
            function () {
                return $this->modelMock->getId();
            },
            $this->adapterMock
        );
    }
}
