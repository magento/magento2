<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Model\Indexer\Rule;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductIndexer;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleProductIndexerTest extends TestCase
{
    /**
     * @var IndexBuilder|MockObject
     */
    protected $indexBuilder;

    /**
     * @var RuleProductIndexer
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
            RuleProductIndexer::class,
            [
                'indexBuilder' => $this->indexBuilder,
            ]
        );

        $this->cacheContextMock = $this->createMock(CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            RuleProductIndexer::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->indexer, $this->cacheContextMock);
    }

    public function testDoExecuteList()
    {
        $ids = [1, 2, 5];
        $this->indexBuilder->expects($this->once())->method('reindexFull');
        $this->cacheContextMock->expects($this->once())
            ->method('registerTags')
            ->with(
                [
                    Category::CACHE_TAG,
                    Product::CACHE_TAG,
                    Block::CACHE_TAG
                ]
            );
        $this->indexer->executeList($ids);
    }

    public function testDoExecuteRow()
    {
        $this->indexBuilder->expects($this->once())->method('reindexFull');

        $this->indexer->executeRow(5);
    }
}
