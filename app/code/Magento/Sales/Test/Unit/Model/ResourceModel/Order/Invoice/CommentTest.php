<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Invoice;

/**
 * Class CommentTest
 */
class CommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment
     */
    protected $commentResource;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentModelMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Comment\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshotMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->commentModelMock = $this->getMock(
            \Magento\Sales\Model\Order\Invoice\Comment::class,
            [],
            [],
            '',
            false
        );
        $this->appResourceMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );
        $this->connectionMock = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            [],
            [],
            '',
            false
        );
        $this->validatorMock = $this->getMock(
            \Magento\Sales\Model\Order\Invoice\Comment\Validator::class,
            [],
            [],
            '',
            false
        );
        $this->entitySnapshotMock = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class,
            [],
            [],
            '',
            false
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnValue([]));
        $this->connectionMock->expects($this->any())
            ->method('insert');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');
        $this->commentModelMock->expects($this->any())->method('hasDataChanges')->will($this->returnValue(true));
        $this->commentModelMock->expects($this->any())->method('isSaveAllowed')->will($this->returnValue(true));

        $relationProcessorMock = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class,
            [],
            [],
            '',
            false
        );

        $contextMock = $this->getMock(\Magento\Framework\Model\ResourceModel\Db\Context::class, [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->appResourceMock);
        $contextMock->expects($this->once())->method('getObjectRelationProcessor')->willReturn($relationProcessorMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->commentResource = $objectManager->getObject(
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment::class,
            [
                'context' => $contextMock,
                'validator' => $this->validatorMock,
                'entitySnapshot' => $this->entitySnapshotMock
            ]
        );
    }

    /**
     * Test _beforeSaveMethod via save()
     */
    public function testSave()
    {
        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->commentModelMock)
            ->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->commentModelMock))
            ->will($this->returnValue([]));
        $this->commentModelMock->expects($this->any())->method('getData')->willReturn([]);
        $this->commentResource->save($this->commentModelMock);
        $this->assertTrue(true);
    }

    /**
     * Test _beforeSaveMethod via save() with failed validation
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot save comment:
     */
    public function testSaveValidationFailed()
    {
        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->commentModelMock)
            ->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->commentModelMock))
            ->will($this->returnValue(['warning message']));
        $this->commentResource->save($this->commentModelMock);
        $this->assertTrue(true);
    }
}
