<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Status;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Status\History\Validator;
use Magento\Sales\Model\ResourceModel\Order\Status\History;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HistoryTest extends TestCase
{
    /**
     * @var History
     */
    protected $historyResource;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|MockObject
     */
    protected $historyMock;

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

        $relationProcessorMock = $this->createMock(
            ObjectRelationProcessor::class
        );

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->appResourceMock);
        $contextMock->expects($this->once())->method('getObjectRelationProcessor')->willReturn($relationProcessorMock);

        $this->historyResource = $objectManager->getObject(
            History::class,
            [
                'context' => $contextMock,
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
        $historyMock = $this->createMock(\Magento\Sales\Model\Order\Status\History::class);
        $this->entitySnapshotMock->expects($this->once())->method('isModified')->with($historyMock)->willReturn(true);
        $historyMock->expects($this->any())->method('isSaveAllowed')->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($historyMock)
            ->willReturn([]);
        $historyMock->expects($this->any())->method('getData')->willReturn([]);
        $this->historyResource->save($historyMock);
    }

    /**
     * test _beforeSaveMethod via save()
     */
    public function testValidate()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Cannot save comment:');
        $historyMock = $this->createMock(\Magento\Sales\Model\Order\Status\History::class);
        $this->entitySnapshotMock->expects($this->once())->method('isModified')->with($historyMock)->willReturn(true);
        $historyMock->expects($this->any())->method('isSaveAllowed')->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($historyMock)
            ->willReturn(['Some warnings']);
        $this->assertEquals($this->historyResource, $this->historyResource->save($historyMock));
    }
}
