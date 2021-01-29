<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product;

class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fullMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Category\Action\RowsFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rowsMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexerMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \Magento\Framework\Indexer\CacheContext|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cacheContextMock;

    protected function setUp(): void
    {
        $this->fullMock = $this->createPartialMock(
            \Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory::class,
            ['create']
        );

        $this->rowsMock = $this->createPartialMock(
            \Magento\Catalog\Model\Indexer\Product\Category\Action\RowsFactory::class,
            ['create']
        );

        $this->indexerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'load', 'isInvalid', 'isWorking', '__wakeup']
        );

        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get']
        );

        $this->model = new \Magento\Catalog\Model\Indexer\Product\Category(
            $this->fullMock,
            $this->rowsMock,
            $this->indexerRegistryMock
        );

        $this->cacheContextMock = $this->createMock(\Magento\Framework\Indexer\CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            \Magento\Catalog\Model\Indexer\Category\Product::class,
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
            \Magento\Catalog\Model\Indexer\Product\Category\Action\Rows::class,
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
            \Magento\Catalog\Model\Indexer\Product\Category\Action\Rows::class,
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
            ->with(\Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }

    public function testExecuteFull()
    {
        /** @var \Magento\Catalog\Model\Indexer\Category\Product\Action\Full $productIndexerFlatFull */
        $productIndexerFlatFull = $this->createMock(\Magento\Catalog\Model\Indexer\Category\Product\Action\Full::class);
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
