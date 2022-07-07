<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\Indexer\Category\Product\Action\Full;
use Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory;
use Magento\Catalog\Model\Indexer\Category\Product\Action\Rows;
use Magento\Catalog\Model\Indexer\Category\Product\Action\RowsFactory;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\IndexMutexInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var Product
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

        $indexMutexMock = $this->createMock(IndexMutexInterface::class);
        $indexMutexMock->method('execute')
            ->willReturnCallback(
                function (string $indexerName, callable $callback) {
                    if ($indexerName) {
                        $callback();
                    }
                }
            );

        $this->model = new Product(
            $this->fullMock,
            $this->rowsMock,
            $this->indexerRegistryMock,
            $indexMutexMock
        );

        $this->cacheContextMock = $this->createMock(CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            Product::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->model, $this->cacheContextMock);
    }

    /**
     * @return void
     */
    public function testExecuteWithIndexerWorking(): void
    {
        $ids = [1, 2, 3];

        $this->prepareIndexer();

        $rowMock = $this->createPartialMock(
            Rows::class,
            ['execute']
        );
        $rowMock
            ->method('execute')
            ->with($ids)
            ->willReturn($rowMock);

        $this->rowsMock->expects($this->once())->method('create')->willReturn($rowMock);

        $this->model->execute($ids);
    }

    /**
     * @return void
     */
    public function testExecuteWithIndexerNotWorking(): void
    {
        $ids = [1, 2, 3];

        $this->prepareIndexer();

        $rowMock = $this->createPartialMock(
            Rows::class,
            ['execute']
        );
        $rowMock->expects($this->once())->method('execute')->with($ids)->willReturnSelf();

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
        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(Product::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }

    /**
     * @return void
     */
    public function testExecuteFull(): void
    {
        /** @var Full $productIndexerFlatFull */
        $productIndexerFlatFull = $this->createMock(Full::class);
        $this->fullMock->expects($this->once())
            ->method('create')
            ->willReturn($productIndexerFlatFull);
        $productIndexerFlatFull->expects($this->once())
            ->method('execute');
        $this->cacheContextMock->expects($this->once())
            ->method('registerTags')
            ->with([Category::CACHE_TAG]);
        $this->model->executeFull();
    }
}
