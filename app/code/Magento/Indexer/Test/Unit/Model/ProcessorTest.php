<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Mview\ProcessorInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\Collection;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\Processor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    protected $model;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var IndexerInterfaceFactory|MockObject
     */
    protected $indexerFactoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $indexersFactoryMock;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $viewProcessorMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(
            ConfigInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getIndexers']
        );
        $this->indexerFactoryMock = $this->createPartialMock(
            IndexerInterfaceFactory::class,
            ['create']
        );
        $this->indexersFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->viewProcessorMock = $this->getMockForAbstractClass(
            ProcessorInterface::class,
            [],
            '',
            false
        );
        $this->model = new Processor(
            $this->configMock,
            $this->indexerFactoryMock,
            $this->indexersFactoryMock,
            $this->viewProcessorMock
        );
    }

    public function testReindexAllInvalid()
    {
        $indexers = ['indexer1' => [], 'indexer2' => []];

        $this->configMock->expects($this->once())->method('getIndexers')->willReturn($indexers);

        $state1Mock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
        $state1Mock->expects(
            $this->once()
        )->method(
            'getStatus'
        )->willReturn(
            StateInterface::STATUS_INVALID
        );
        $indexer1Mock = $this->createPartialMock(
            Indexer::class,
            ['load', 'getState', 'reindexAll']
        );
        $indexer1Mock->expects($this->once())->method('getState')->willReturn($state1Mock);
        $indexer1Mock->expects($this->once())->method('reindexAll');

        $state2Mock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
        $state2Mock->expects(
            $this->once()
        )->method(
            'getStatus'
        )->willReturn(
            StateInterface::STATUS_VALID
        );
        $indexer2Mock = $this->createPartialMock(
            Indexer::class,
            ['load', 'getState', 'reindexAll']
        );
        $indexer2Mock->expects($this->never())->method('reindexAll');
        $indexer2Mock->expects($this->once())->method('getState')->willReturn($state2Mock);

        $this->indexerFactoryMock->expects($this->at(0))->method('create')->willReturn($indexer1Mock);
        $this->indexerFactoryMock->expects($this->at(1))->method('create')->willReturn($indexer2Mock);

        $this->model->reindexAllInvalid();
    }

    public function testReindexAll()
    {
        $indexerMock = $this->createMock(Indexer::class);
        $indexerMock->expects($this->exactly(2))->method('reindexAll');
        $indexers = [$indexerMock, $indexerMock];

        $indexersMock = $this->createMock(Collection::class);
        $this->indexersFactoryMock->expects($this->once())->method('create')->willReturn($indexersMock);
        $indexersMock->expects($this->once())->method('getItems')->willReturn($indexers);

        $this->model->reindexAll();
    }

    public function testUpdateMview()
    {
        $this->viewProcessorMock->expects($this->once())->method('update')->with('indexer')->willReturnSelf();
        $this->model->updateMview();
    }

    public function testClearChangelog()
    {
        $this->viewProcessorMock->expects($this->once())->method('clearChangelog')->with('indexer')->willReturnSelf();
        $this->model->clearChangelog();
    }
}
