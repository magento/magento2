<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Indexer\Design\Config\Plugin;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Store as StoreModel;
use Magento\Theme\Model\Data\Design\Config;
use Magento\Theme\Model\Indexer\Design\Config\Plugin\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    /** @var Store */
    private $model;

    /** @var IndexerRegistry|MockObject */
    private $indexerRegistryMock;

    protected function setUp(): void
    {
        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Store($this->indexerRegistryMock);
    }

    public function testAfterSave(): void
    {
        /** @var StoreModel|MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder(StoreModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subjectMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(true);

        /** @var IndexerInterface|MockObject $indexerMock */
        $indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $indexerMock->expects($this->once())
            ->method('invalidate');

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Config::DESIGN_CONFIG_GRID_INDEXER_ID)
            ->willReturn($indexerMock);

        $this->assertSame($subjectMock, $this->model->afterSave($subjectMock, $subjectMock));
    }

    public function testAfterSaveWithExistentSubject(): void
    {
        /** @var StoreModel|MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder(StoreModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subjectMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(false);

        $this->indexerRegistryMock->expects($this->never())
            ->method('get');

        $this->assertSame($subjectMock, $this->model->afterSave($subjectMock, $subjectMock));
    }

    public function testAfterDelete(): void
    {
        /** @var StoreModel|MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder(StoreModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var IndexerInterface|MockObject $indexerMock */
        $indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $indexerMock->expects($this->once())
            ->method('invalidate');

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Config::DESIGN_CONFIG_GRID_INDEXER_ID)
            ->willReturn($indexerMock);

        $this->assertSame($subjectMock, $this->model->afterDelete($subjectMock, $subjectMock));
    }
}
