<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ItemTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Item
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var RelationComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationCompositeMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectRelationProcessorMock;

    /**
     * Mock class dependencies
     */
    protected function setUp()
    {
        $this->resourceMock = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->quoteItemMock = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
        $this->connectionMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            [
                'describeTable',
                'insert',
                'lastInsertId',
                'beginTransaction',
                'rollback',
                'commit',
                'quoteInto',
                'update'
            ],
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
        $this->relationCompositeMock = $this->getMock(
            'Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite',
            [],
            [],
            '',
            false
        );
        $this->objectRelationProcessorMock = $this->getMock(
            'Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor',
            [],
            [],
            '',
            false
        );
        $contextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->objectRelationProcessorMock);

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\ResourceModel\Quote\Item',
            [
                'context' => $contextMock,
                'entitySnapshot' => $this->entitySnapshotMock,
                'entityRelationComposite' => $this->relationCompositeMock
            ]
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb', $this->model);
    }

    public function testSaveNotModifiedItem()
    {
        $this->entitySnapshotMock->expects($this->exactly(2))
            ->method('isModified')
            ->with($this->quoteItemMock)
            ->willReturn(false);

        $this->quoteItemMock->expects($this->never())
            ->method('isOptionsSaved');
        $this->quoteItemMock->expects($this->never())
            ->method('saveItemOptions');

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->assertEquals($this->model, $this->model->save($this->quoteItemMock));
    }

    public function testSaveSavedBeforeItem()
    {
        $this->entitySnapshotMock->expects($this->exactly(2))
            ->method('isModified')
            ->with($this->quoteItemMock)
            ->willReturn(true);

        $this->quoteItemMock->expects($this->once())
            ->method('isOptionsSaved')
            ->willReturn(true);
        $this->quoteItemMock->expects($this->never())
            ->method('saveItemOptions');

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->assertEquals($this->model, $this->model->save($this->quoteItemMock));
    }

    public function testSaveModifiedItem()
    {
        $this->entitySnapshotMock->expects($this->exactly(2))
            ->method('isModified')
            ->with($this->quoteItemMock)
            ->willReturn(true);

        $this->quoteItemMock->expects($this->once())
            ->method('isOptionsSaved')
            ->willReturn(false);
        $this->quoteItemMock->expects($this->once())
            ->method('saveItemOptions');

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->assertEquals($this->model, $this->model->save($this->quoteItemMock));
    }
}
