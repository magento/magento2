<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Indexer\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProductRuleIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer
     */
    protected $indexer;

    /**
     * @var \Magento\Framework\Indexer\CacheContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheContextMock;

    protected function setUp()
    {
        $this->indexBuilder = $this->getMock('Magento\CatalogRule\Model\Indexer\IndexBuilder', [], [], '', false);

        $this->indexer = (new ObjectManager($this))->getObject(
            'Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer',
            [
                'indexBuilder' => $this->indexBuilder,
            ]
        );

        $this->cacheContextMock = $this->getMock(\Magento\Framework\Indexer\CacheContext::class, [], [], '', false);

        $cacheContextProperty = new \ReflectionProperty(
            \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer::class,
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
            ->with(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
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
