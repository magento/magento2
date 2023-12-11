<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Category\Flat\Plugin\StoreGroup;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Group as GroupModel;
use Magento\Store\Model\ResourceModel\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreGroupTest extends TestCase
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
     * @var StoreGroup
     */
    private $model;

    /**
     * @var MockObject|Group
     */
    private $subjectMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var MockObject|GroupModel
     */
    private $groupMock;

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
        $this->stateMock = $this->createPartialMock(State::class, ['isFlatEnabled']);
        $this->subjectMock = $this->createMock(Group::class);

        $this->groupMock = $this->createPartialMock(
            GroupModel::class,
            ['dataHasChangedFor', 'isObjectNew']
        );

        $this->indexerRegistryMock = $this->createPartialMock(IndexerRegistry::class, ['get']);

        $this->model = new StoreGroup($this->indexerRegistryMock, $this->stateMock);
    }

    public function testAfterSave(): void
    {
        $this->stateMock->expects($this->once())->method('isFlatEnabled')->willReturn(true);
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(State::INDEXER_ID)
            ->willReturn($this->indexerMock);
        $this->groupMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('root_category_id')
            ->willReturn(true);
        $this->groupMock->expects($this->once())->method('isObjectNew')->willReturn(false);

        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $this->groupMock)
        );
    }

    public function testAfterSaveNotNew(): void
    {
        $this->stateMock->expects($this->never())->method('isFlatEnabled');
        $this->groupMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('root_category_id')
            ->willReturn(true);
        $this->groupMock->expects($this->once())->method('isObjectNew')->willReturn(true);

        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $this->groupMock)
        );
    }
}
