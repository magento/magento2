<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product;

use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\Indexer\Category\Product\Action\Full;
use Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory;
use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\Catalog\Model\Indexer\Product\Category\Action\Rows;
use Magento\Catalog\Model\Indexer\Product\Category\Action\RowsFactory;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends TestCase
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

        $this->model = new Category(
            $this->fullMock,
            $this->rowsMock,
            $this->indexerRegistryMock
        );

        $this->cacheContextMock = $this->createMock(CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            Product::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->model, $this->cacheContextMock);
    }

    public function testExecuteWithIndexerWorking()
    {
        $ids = [1, 2, 3];

        $this->prepareIndexer();

        $rowMock = $this->createPartialMock(
            Rows::class,
            ['execute']
        );
        $rowMock->expects($this->at(0))->method('execute')->with($ids)->willReturnSelf();

        $this->rowsMock->expects($this->once())->method('create')->willReturn($rowMock);

        $this->model->execute($ids);
    }

    public function testExecuteWithIndexerNotWorking()
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
            ->with(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);

        $this->model->execute($ids);
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(Category::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }

    public function testExecuteFull()
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
            ->with(
                [
                    \Magento\Catalog\Model\Category::CACHE_TAG,
                    \Magento\Catalog\Model\Product::CACHE_TAG
                ]
            );
        $this->model->executeFull();
    }
}
