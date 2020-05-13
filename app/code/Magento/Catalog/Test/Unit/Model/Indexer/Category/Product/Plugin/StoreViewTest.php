<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\Indexer\Category\Product\Plugin\StoreView;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreViewTest extends TestCase
{
    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var MockObject|IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var StoreView
     */
    protected $model;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var TableMaintainer|MockObject
     */
    protected $tableMaintainer;

    /**
     * @var MockObject
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->indexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState']
        );
        $this->subject = $this->createMock(Group::class);
        $this->indexerRegistryMock = $this->createPartialMock(IndexerRegistry::class, ['get']);
        $this->storeMock = $this->createPartialMock(
            Store::class,
            [
                'isObjectNew',
                'getId',
                'dataHasChangedFor'
            ]
        );
        $this->tableMaintainer = $this->createPartialMock(
            TableMaintainer::class,
            [
                'createTablesForStore'
            ]
        );

        $this->model = new StoreView($this->indexerRegistryMock, $this->tableMaintainer);
    }

    public function testAroundSaveNewObject()
    {
        $this->mockIndexerMethods();
        $this->storeMock->expects($this->atLeastOnce())->method('isObjectNew')->willReturn(true);
        $this->storeMock->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->model->beforeSave($this->subject, $this->storeMock);
        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->storeMock));
    }

    public function testAroundSaveHasChanged()
    {
        $this->mockIndexerMethods();
        $this->storeMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('group_id')
            ->willReturn(true);
        $this->model->beforeSave($this->subject, $this->storeMock);
        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->storeMock));
    }

    public function testAroundSaveNoNeed()
    {
        $this->storeMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('group_id')
            ->willReturn(false);
        $this->model->beforeSave($this->subject, $this->storeMock);
        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->storeMock));
    }

    private function mockIndexerMethods()
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Product::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }
}
