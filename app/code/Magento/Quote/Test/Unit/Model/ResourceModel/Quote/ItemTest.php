<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\ResourceModel\Quote\Item;

/**
 * Class ItemTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends \PHPUnit\Framework\TestCase
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
        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->quoteItemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->connectionMock = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [
                'describeTable',
                'insert',
                'lastInsertId',
                'beginTransaction',
                'rollback',
                'commit',
                'quoteInto',
                'update'
            ]);
        $this->entitySnapshotMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class
        );
        $this->relationCompositeMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite::class
        );
        $this->objectRelationProcessorMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class
        );
        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->objectRelationProcessorMock);

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Quote\Model\ResourceModel\Quote\Item::class,
            [
                'context' => $contextMock,
                'entitySnapshot' => $this->entitySnapshotMock,
                'entityRelationComposite' => $this->relationCompositeMock
            ]
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb::class,
            $this->model
        );
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
