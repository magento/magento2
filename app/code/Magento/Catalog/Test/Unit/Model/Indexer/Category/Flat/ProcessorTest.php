<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat;

use Magento\Catalog\Model\Indexer\Category\Flat\Processor;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var IndexerInterface|MockObject
     */
    private $indexerMock;

    protected function setUp(): void
    {
        $indexerRegistryMock = $this->createMock(IndexerRegistry::class);
        $this->stateMock = $this->createMock(State::class);
        $this->processor = new Processor($indexerRegistryMock, $this->stateMock);

        $this->indexerMock = $this->createMock(IndexerInterface::class);
        $indexerRegistryMock->method('get')
            ->with(State::INDEXER_ID)
            ->willReturn($this->indexerMock);
        $this->indexerMock->method('isScheduled')
            ->willReturn(false);
    }

    /**
     * @dataProvider stateDataProvider
     * @param bool $isFlatEnabled
     * @param int $numMethodCalled
     * @return void
     */
    public function testReindexRow(bool $isFlatEnabled, int $numMethodCalled): void
    {
        $id = 123;

        $this->stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->willReturn($isFlatEnabled);
        $this->indexerMock->expects($this->exactly($numMethodCalled))
            ->method('reindexRow')
            ->with($id);

        $this->processor->reindexRow($id);
    }

    /**
     * @dataProvider stateDataProvider
     * @param bool $isFlatEnabled
     * @param int $numMethodCalled
     * @return void
     */
    public function testReindexList(bool $isFlatEnabled, int $numMethodCalled): void
    {
        $ids = [123];

        $this->stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->willReturn($isFlatEnabled);
        $this->indexerMock->expects($this->exactly($numMethodCalled))
            ->method('reindexList')
            ->with($ids);

        $this->processor->reindexList($ids);
    }

    /**
     * @dataProvider stateDataProvider
     * @param bool $isFlatEnabled
     * @param int $numMethodCalled
     * @return void
     */
    public function testReindexAll(bool $isFlatEnabled, int $numMethodCalled): void
    {
        $this->stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->willReturn($isFlatEnabled);
        $this->indexerMock->expects($this->exactly($numMethodCalled))
            ->method('reindexAll');

        $this->processor->reindexAll();
    }

    /**
     * @dataProvider stateDataProvider
     * @param bool $isFlatEnabled
     * @param int $numMethodCalled
     * @return void
     */
    public function testMarkIndexerAsInvalid(bool $isFlatEnabled, int $numMethodCalled): void
    {
        $this->stateMock->expects($this->once())
            ->method('isFlatEnabled')
            ->willReturn($isFlatEnabled);
        $this->indexerMock->expects($this->exactly($numMethodCalled))
            ->method('invalidate');

        $this->processor->markIndexerAsInvalid();
    }

    /**
     * @return array[]
     */
    public static function stateDataProvider(): array
    {
        return [
            [true, 1],
            [false, 0],
        ];
    }
}
