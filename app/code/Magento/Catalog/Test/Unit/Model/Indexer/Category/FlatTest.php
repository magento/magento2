<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Category\Flat;
use Magento\Catalog\Model\Indexer\Category\Flat\Action\Full;
use Magento\Catalog\Model\Indexer\Category\Flat\Action\FullFactory;
use Magento\Catalog\Model\Indexer\Category\Flat\Action\Rows;
use Magento\Catalog\Model\Indexer\Category\Flat\Action\RowsFactory;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FlatTest extends TestCase
{
    /**
     * @var Flat
     */
    protected $model;

    /**
     * @var FullFactory|MockObject
     */
    protected $fullMock;

    /**
     * @var RowsFactory|MockObject
     */
    protected $rowsMock;

    /**
     * @var IndexerInterface|MockObject
     */
    protected $indexerMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var CacheContext|MockObject
     */
    protected $cacheContextMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fullMock = $this->createPartialMock(
            FullFactory::class,
            ['create']
        );

        $this->rowsMock = $this->createPartialMock(
            RowsFactory::class,
            ['create']
        );

        $this->indexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'load', 'isInvalid', 'isWorking']
        );

        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );

        $this->model = new Flat(
            $this->fullMock,
            $this->rowsMock,
            $this->indexerRegistryMock
        );

        $this->cacheContextMock = $this->createMock(CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            Flat::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->model, $this->cacheContextMock);
    }

    /**
     * @return void
     */
    public function testExecuteWithIndexerInvalid(): void
    {
        $this->indexerMock->expects($this->once())->method('isInvalid')->willReturn(true);
        $this->prepareIndexer();

        $this->rowsMock->expects($this->never())->method('create');

        $this->model->execute([1, 2, 3]);
    }

    /**
     * @return void
     */
    public function testExecuteWithIndexerWorking(): void
    {
        $ids = [1, 2, 3];

        $this->indexerMock->expects($this->once())->method('isInvalid')->willReturn(false);
        $this->indexerMock->expects($this->once())->method('isWorking')->willReturn(true);
        $this->prepareIndexer();

        $rowMock = $this->createPartialMock(
            Rows::class,
            ['reindex']
        );
        $rowMock
            ->method('reindex')
            ->willReturnCallback(function ($arg1, $arg2) use ($ids, $rowMock) {
                if ($arg1 == $ids && ($arg2 == true || $arg2 == false)) {
                    return $rowMock;
                }
            });

        $this->rowsMock->expects($this->once())->method('create')->willReturn($rowMock);

        $this->cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(Category::CACHE_TAG, $ids);

        $this->model->execute($ids);
    }

    /**
     * @return void
     */
    public function testExecuteWithIndexerNotWorking(): void
    {
        $ids = [1, 2, 3];

        $this->indexerMock->expects($this->once())->method('isInvalid')->willReturn(false);
        $this->indexerMock->expects($this->once())->method('isWorking')->willReturn(false);
        $this->prepareIndexer();

        $rowMock = $this->createPartialMock(
            Rows::class,
            ['reindex']
        );
        $rowMock->expects($this->once())->method('reindex')->with($ids, false)->willReturnSelf();

        $this->rowsMock->expects($this->once())->method('create')->willReturn($rowMock);

        $this->cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(Category::CACHE_TAG, $ids);

        $this->model->execute($ids);
    }

    /**
     * @return void
     */
    protected function prepareIndexer(): void
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(State::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }

    /**
     * @return void
     */
    public function testExecuteFull(): void
    {
        /** @var Full $categoryIndexerFlatFull */
        $categoryIndexerFlatFull = $this->createMock(Full::class);
        $this->fullMock->expects($this->once())
            ->method('create')
            ->willReturn($categoryIndexerFlatFull);
        $categoryIndexerFlatFull->expects($this->once())
            ->method('reindexAll');
        $this->cacheContextMock->expects($this->once())
            ->method('registerTags')
            ->with([Category::CACHE_TAG]);
        $this->model->executeFull();
    }
}
