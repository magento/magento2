<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends TestCase
{
    /**
     * @var Item
     */
    protected $model;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var QuoteItem|MockObject
     */
    protected $quoteItemMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var Snapshot|MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var RelationComposite|MockObject
     */
    protected $relationCompositeMock;

    /**
     * @var ObjectRelationProcessor|MockObject
     */
    protected $objectRelationProcessorMock;

    /**
     * Mock class dependencies
     */
    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->quoteItemMock = $this->createMock(QuoteItem::class);
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(
                [
                    'rollback',
                    'describeTable',
                    'insert',
                    'lastInsertId',
                    'beginTransaction',
                    'commit',
                    'quoteInto',
                    'update'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->entitySnapshotMock = $this->createMock(
            Snapshot::class
        );
        $this->relationCompositeMock = $this->createMock(
            RelationComposite::class
        );
        $this->objectRelationProcessorMock = $this->createMock(
            ObjectRelationProcessor::class
        );
        $contextMock = $this->createMock(Context::class);
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
            AbstractDb::class,
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
