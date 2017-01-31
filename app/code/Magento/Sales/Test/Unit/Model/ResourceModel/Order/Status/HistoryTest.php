<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Status;

/**
 * Class HistoryTest
 * @package Magento\Sales\Model\ResourceModel\Order\Status
 */
class HistoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\History
     */
    protected $historyResource;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshotMock;

    protected function setUp()
    {
        $this->appResourceMock = $this->getMock(
            'Magento\Framework\App\ResourceConnection',
            [],
            [],
            '',
            false
        );
        $this->connectionMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [],
            [],
            '',
            false
        );
        $this->validatorMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\History\Validator',
            [],
            [],
            '',
            false
        );
        $this->entitySnapshotMock = $this->getMock(
            'Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot',
            [],
            [],
            '',
            false
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnValue([]));
        $this->connectionMock->expects($this->any())
            ->method('insert');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');

        $relationProcessorMock = $this->getMock(
            '\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor',
            [],
            [],
            '',
            false
        );

        $contextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->appResourceMock);
        $contextMock->expects($this->once())->method('getObjectRelationProcessor')->willReturn($relationProcessorMock);

        $this->historyResource = $objectManager->getObject(
            'Magento\Sales\Model\ResourceModel\Order\Status\History',
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
        $historyMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\History',
            [],
            [],
            '',
            false
        );
        $this->entitySnapshotMock->expects($this->once())->method('isModified')->with($historyMock)->willReturn(true);
        $historyMock->expects($this->any())->method('isSaveAllowed')->will($this->returnValue(true));
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($historyMock)
            ->will($this->returnValue([]));
        $historyMock->expects($this->any())->method('getData')->willReturn([]);
        $this->historyResource->save($historyMock);
    }

    /**
     * test _beforeSaveMethod via save()
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot save comment:
     */
    public function testValidate()
    {
        $historyMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\History',
            [],
            [],
            '',
            false
        );
        $this->entitySnapshotMock->expects($this->once())->method('isModified')->with($historyMock)->willReturn(true);
        $historyMock->expects($this->any())->method('isSaveAllowed')->will($this->returnValue(true));
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($historyMock)
            ->will($this->returnValue(['Some warnings']));
        $this->assertEquals($this->historyResource, $this->historyResource->save($historyMock));
    }
}
