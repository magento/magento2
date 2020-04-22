<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductRuleIndexerTest extends TestCase
{
    /**
     * @var IndexBuilder|MockObject
     */
    protected $indexBuilder;

    /**
     * @var ProductRuleIndexer
     */
    protected $indexer;

    /**
     * @var CacheContext|MockObject
     */
    protected $cacheContextMock;

    protected function setUp(): void
    {
        $this->indexBuilder = $this->createMock(IndexBuilder::class);

        $this->indexer = (new ObjectManager($this))->getObject(
            ProductRuleIndexer::class,
            [
                'indexBuilder' => $this->indexBuilder,
            ]
        );

        $this->cacheContextMock = $this->createMock(CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            ProductRuleIndexer::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->indexer, $this->cacheContextMock);
    }

    /**
     * @param array $ids
     * @param array $idsForIndexer
     * @dataProvider dataProviderForExecuteList
     */
    public function testDoExecuteList($ids, $idsForIndexer)
    {
        $this->indexBuilder->expects($this->once())
            ->method('reindexByIds')
            ->with($idsForIndexer);
        $this->cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(Product::CACHE_TAG, $ids);
        $this->indexer->executeList($ids);
    }

    /**
     * @return array
     */
    public function dataProviderForExecuteList()
    {
        return [
            [
                [1, 2, 3, 2, 3],
                [1, 2, 3],
            ],
            [
                [1, 2, 3],
                [1, 2, 3],
            ],
        ];
    }

    public function testDoExecuteRow()
    {
        $id = 5;
        $this->indexBuilder->expects($this->once())->method('reindexById')->with($id);

        $this->indexer->executeRow($id);
    }
}
