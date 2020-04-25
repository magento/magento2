<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Indexer\Design\Config\Plugin;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Theme\Model\Data\Design\Config;
use Magento\Theme\Model\Indexer\Design\Config\Plugin\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    /** @var Store */
    protected $model;

    /** @var IndexerRegistry|MockObject */
    protected $indexerRegistryMock;

    protected function setUp(): void
    {
        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Store($this->indexerRegistryMock);
    }

    public function testAroundSave()
    {
        $subjectId = 0;

        /** @var \Magento\Store\Model\Store|MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subjectMock->expects($this->once())
            ->method('getId')
            ->willReturn($subjectId);

        $closureMock = function () use ($subjectMock) {
            return $subjectMock;
        };

        /** @var IndexerInterface|MockObject $indexerMock */
        $indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $indexerMock->expects($this->once())
            ->method('invalidate');

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Config::DESIGN_CONFIG_GRID_INDEXER_ID)
            ->willReturn($indexerMock);

        $this->assertEquals($subjectMock, $this->model->aroundSave($subjectMock, $closureMock));
    }

    public function testAroundSaveWithExistentSubject()
    {
        $subjectId = 1;

        /** @var \Magento\Store\Model\Store|MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subjectMock->expects($this->once())
            ->method('getId')
            ->willReturn($subjectId);

        $closureMock = function () use ($subjectMock) {
            return $subjectMock;
        };

        $this->indexerRegistryMock->expects($this->never())
            ->method('get');

        $this->assertEquals($subjectMock, $this->model->aroundSave($subjectMock, $closureMock));
    }

    public function testAfterDelete()
    {
        /** @var \Magento\Store\Model\Store|MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
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

        $this->assertEquals($subjectMock, $this->model->afterDelete($subjectMock, $subjectMock));
    }
}
