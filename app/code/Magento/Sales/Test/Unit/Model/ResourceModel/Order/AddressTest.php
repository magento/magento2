<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Validator;
use Magento\Sales\Model\ResourceModel\Order\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    protected $addressResource;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Sales\Model\Order\Address|MockObject
     */
    protected $addressMock;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var Validator|MockObject
     */
    protected $validatorMock;

    /**
     * @var Snapshot|MockObject
     */
    protected $entitySnapshotMock;

    protected function setUp(): void
    {
        $this->addressMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getParentId', 'hasDataChanges', 'beforeSave', 'afterSave', 'validateBeforeSave', 'getOrder']
        );
        $this->orderMock = $this->createPartialMock(Order::class, ['getId']);
        $this->appResourceMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->validatorMock = $this->createMock(Validator::class);
        $this->entitySnapshotMock = $this->createMock(
            Snapshot::class
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $objectManager = new ObjectManager($this);
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->willReturn([]);
        $this->connectionMock->expects($this->any())
            ->method('insert');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');
        $this->addressResource = $objectManager->getObject(
            Address::class,
            [
                'resource' => $this->appResourceMock,
                'validator' => $this->validatorMock,
                'entitySnapshot' => $this->entitySnapshotMock
            ]
        );
    }

    /**
     * test _beforeSaveMethod via save()
     */
    public function testSave()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->addressMock)
            ->willReturn([]);
        $this->addressMock->expects($this->once())
            ->method('getParentId')
            ->willReturn(1);

        $this->addressResource->save($this->addressMock);
    }

    /**
     * test _beforeSaveMethod via save() with failed validation
     */
    public function testSaveValidationFailed()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('We can\'t save the address:');
        $this->addressMock->expects($this->any())
            ->method('hasDataChanges')
            ->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->addressMock)
            ->willReturn(['warning message']);
        $this->addressResource->save($this->addressMock);
    }
}
