<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MessageQueue\Model\LockFactory;
use Magento\MessageQueue\Model\ResourceModel\Lock as LockResourceModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for lock resource model
 */
class LockTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @var LockResourceModel
     */
    private $lockResourceModel;

    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * @var LockFactory|MockObject
     */
    private $lockFactoryMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->lockFactoryMock = $this->getMockBuilder(LockFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->lockResourceModel = $this->objectManager->getObject(
            LockResourceModel::class,
            [
                'resources' => $this->resourceConnectionMock,
                'dateTime' => $this->dateTimeMock,
                'lockFactory' => $this->lockFactoryMock,
            ]
        );
        parent::setUp();
    }

    public function testReleaseOutdatedLocks()
    {
        /** @var AdapterInterface|MockObject $adapterMock */
        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $tableName = 'queue_lock_mock';
        $this->resourceConnectionMock->expects($this->once())->method('getTableName')->willReturn($tableName);
        $this->dateTimeMock->expects($this->once())->method('gmtTimestamp')->willReturn(1000000000);
        /** Date for timestamp 1000000000 + 86400 */
        $date = new \DateTime('2001-09-09T18:46:40-0700');

        $adapterMock->expects($this->once())->method('delete')->with($tableName, ['created_at <= ?' => $date]);
        $this->lockResourceModel->releaseOutdatedLocks();
    }
}
