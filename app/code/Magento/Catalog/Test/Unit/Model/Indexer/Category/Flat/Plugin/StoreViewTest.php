<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Category\Flat\Plugin\StoreView;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\Store as StoreModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreViewTest extends TestCase
{
    /**
     * @var MockObject|IndexerInterface
     */
    private $indexerMock;

    /**
     * @var MockObject|State
     */
    private $stateMock;

    /**
     * @var StoreView
     */
    private $model;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var Store|MockObject
     */
    private $subjectMock;

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
        $this->stateMock = $this->createPartialMock(
            State::class,
            ['isFlatEnabled']
        );
        $this->subjectMock = $this->createMock(Store::class);
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );
        $this->model = new StoreView($this->indexerRegistryMock, $this->stateMock);
    }

    public function testAfterSaveNewObject(): void
    {
        $this->mockConfigFlatEnabled();
        $this->mockIndexerMethods();
        $storeMock = $this->createPartialMock(
            StoreModel::class,
            ['isObjectNew', 'dataHasChangedFor']
        );
        $storeMock->expects($this->once())->method('isObjectNew')->willReturn(true);

        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $storeMock)
        );
    }

    public function testAfterSaveHasChanged(): void
    {
        $storeMock = $this->createPartialMock(
            StoreModel::class,
            ['isObjectNew', 'dataHasChangedFor']
        );

        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $storeMock)
        );
    }

    public function testAfterSaveNoNeed(): void
    {
        $this->mockConfigFlatEnabledNever();

        $storeMock = $this->createPartialMock(
            StoreModel::class,
            ['isObjectNew', 'dataHasChangedFor']
        );

        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $storeMock)
        );
    }

    private function mockIndexerMethods(): void
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(State::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }

    private function mockConfigFlatEnabled(): void
    {
        $this->stateMock->expects($this->once())->method('isFlatEnabled')->willReturn(true);
    }

    private function mockConfigFlatEnabledNever(): void
    {
        $this->stateMock->expects($this->never())->method('isFlatEnabled');
    }
}
