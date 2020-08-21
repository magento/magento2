<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\Indexer\Category\Product\Plugin\StoreGroup;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Group as GroupModel;
use Magento\Store\Model\ResourceModel\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreGroupTest extends TestCase
{
    /**
     * @var GroupModel|MockObject
     */
    private $groupModelMock;

    /**
     * @var MockObject|IndexerInterface
     */
    private $indexerMock;

    /**
     * @var MockObject|Group
     */
    private $subjectMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var TableMaintainer|MockObject
     */
    private $tableMaintainerMock;

    /**
     * @var StoreGroup
     */
    private $model;

    protected function setUp(): void
    {
        $this->groupModelMock = $this->createPartialMock(
            GroupModel::class,
            ['dataHasChangedFor', 'isObjectNew']
        );
        $this->indexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState']
        );
        $this->subjectMock = $this->createMock(Group::class);
        $this->indexerRegistryMock = $this->createPartialMock(IndexerRegistry::class, ['get']);
        $this->tableMaintainerMock = $this->createMock(TableMaintainer::class);

        $this->model = new StoreGroup($this->indexerRegistryMock, $this->tableMaintainerMock);
    }

    /**
     * @param array $valueMap
     * @dataProvider changedDataProvider
     */
    public function testAfterSave(array $valueMap): void
    {
        $this->mockIndexerMethods();
        $this->groupModelMock->expects($this->exactly(2))->method('dataHasChangedFor')->willReturnMap($valueMap);
        $this->groupModelMock->expects($this->once())->method('isObjectNew')->willReturn(false);

        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $this->groupModelMock)
        );
    }

    /**
     * @param array $valueMap
     * @dataProvider changedDataProvider
     */
    public function testAfterSaveNotNew(array $valueMap): void
    {
        $this->groupModelMock->expects($this->exactly(2))->method('dataHasChangedFor')->willReturnMap($valueMap);
        $this->groupModelMock->expects($this->once())->method('isObjectNew')->willReturn(true);

        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $this->groupModelMock)
        );
    }

    /**
     * @return array
     */
    public function changedDataProvider(): array
    {
        return [
            [
                [['root_category_id', true], ['website_id', false]],
                [['root_category_id', false], ['website_id', true]],
            ]
        ];
    }

    public function testAfterSaveWithoutChanges(): void
    {
        $this->groupModelMock->expects($this->exactly(2))
            ->method('dataHasChangedFor')
            ->willReturnMap([['root_category_id', false], ['website_id', false]]);
        $this->groupModelMock->expects($this->never())->method('isObjectNew');

        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $this->groupModelMock)
        );
    }

    private function mockIndexerMethods(): void
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Product::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }
}
