<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class AbstractProcessorTest extends TestCase
{
    use MockCreationTrait;

    private const INDEXER_ID = 'stub_indexer_id';

    /**
     * @var AbstractProcessorStub
     */
    protected $model;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $_indexerRegistryMock;

    protected function setUp(): void
    {
        $this->_indexerRegistryMock = $this->createPartialMockWithReflection(
            IndexerRegistry::class,
            ['isScheduled', 'reindexRow', 'reindexList', 'reindexAll', 'invalidate', 'get']
        );
        $this->model = new AbstractProcessorStub(
            $this->_indexerRegistryMock
        );
    }

    public function testGetIndexer()
    {
        $this->_indexerRegistryMock->expects($this->once())->method('get')->with(
            self::INDEXER_ID
        )->willReturnSelf();
        $this->model->getIndexer();
    }

    public function testReindexAll()
    {
        $this->_indexerRegistryMock->expects($this->once())->method('get')->with(
            self::INDEXER_ID
        )->willReturnSelf();
        $this->_indexerRegistryMock->expects($this->once())->method('reindexAll')->willReturnSelf();
        $this->model->reindexAll();
    }

    public function testMarkIndexerAsInvalid()
    {
        $this->_indexerRegistryMock->expects($this->once())->method('get')->with(
            self::INDEXER_ID
        )->willReturnSelf();
        $this->_indexerRegistryMock->expects($this->once())->method('invalidate')->willReturnSelf();
        $this->model->markIndexerAsInvalid();
    }

    public function testGetIndexerId()
    {
        $this->assertEquals(self::INDEXER_ID, $this->model->getIndexerId());
    }

    /**
     * @param bool $scheduled
     */
    #[DataProvider('runDataProvider')]
    public function testReindexRow($scheduled)
    {
        $id = 1;
        if ($scheduled) {
            $this->_indexerRegistryMock->expects($this->once())->method('get')->with(
                self::INDEXER_ID
            )->willReturnSelf();
            $this->_indexerRegistryMock->expects($this->once())->method('isScheduled')->willReturn($scheduled);
            $this->assertNull($this->model->reindexRow($id));
        } else {
            $this->_indexerRegistryMock->expects($this->exactly(2))->method('get')->with(
                self::INDEXER_ID
            )->willReturnSelf();
            $this->_indexerRegistryMock->expects($this->once())->method('isScheduled')->willReturn($scheduled);
            $this->_indexerRegistryMock->expects($this->once())->method('reindexRow')->with($id)->willReturnSelf();
            $this->assertNull($this->model->reindexRow($id));
        }
    }

    /**
     * @param bool $scheduled
     */
    #[DataProvider('runDataProvider')]
    public function testReindexList($scheduled)
    {
        $ids = [1];
        if ($scheduled) {
            $this->_indexerRegistryMock->expects($this->once())->method('get')->with(
                self::INDEXER_ID
            )->willReturnSelf();
            $this->_indexerRegistryMock->expects($this->once())->method('isScheduled')->willReturn($scheduled);
            $this->assertNull($this->model->reindexList($ids));
        } else {
            $this->_indexerRegistryMock->expects($this->exactly(2))->method('get')->with(
                self::INDEXER_ID
            )->willReturnSelf();
            $this->_indexerRegistryMock->expects($this->once())->method('isScheduled')->willReturn($scheduled);
            $this->_indexerRegistryMock->expects($this->once())->method('reindexList')->with($ids)->willReturnSelf();
            $this->assertNull($this->model->reindexList($ids));
        }
    }

    /**
     * @return array
     */
    public static function runDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Test isIndexerScheduled()
     */
    public function testIsIndexerScheduled()
    {
        $this->_indexerRegistryMock->expects($this->once())->method('get')->with(
            AbstractProcessorStub::INDEXER_ID
        )->willReturnSelf();
        $this->_indexerRegistryMock->expects($this->once())->method('isScheduled')->willReturn(false);
        $this->model->isIndexerScheduled();
    }
}
