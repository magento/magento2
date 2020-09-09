<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Product\Flat;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Full;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Row;
use Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows;
use Magento\Catalog\Model\Product;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FlatTest extends TestCase
{
    /**
     * @var Flat
     */
    private $model;

    /**
     * @var Row|MockObject
     */
    private $productFlatIndexerRow;

    /**
     * @var Rows|MockObject
     */
    private $productFlatIndexerRows;

    /**
     * @var Full|MockObject
     */
    private $productFlatIndexerFull;

    /**
     * @var CacheContext|MockObject
     */
    protected $cacheContextMock;

    protected function setUp(): void
    {
        $this->productFlatIndexerRow = $this->getMockBuilder(
            Row::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFlatIndexerRows = $this->getMockBuilder(
            Rows::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFlatIndexerFull = $this->getMockBuilder(
            Full::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Flat::class,
            [
                'productFlatIndexerRow' => $this->productFlatIndexerRow,
                'productFlatIndexerRows' => $this->productFlatIndexerRows,
                'productFlatIndexerFull' => $this->productFlatIndexerFull
            ]
        );

        $this->cacheContextMock = $this->createMock(CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            Flat::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->model, $this->cacheContextMock);
    }

    public function testExecute()
    {
        $ids = [1, 2, 3];
        $this->productFlatIndexerRows->expects($this->any())->method('execute')->with($ids);

        $this->cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(Product::CACHE_TAG, $ids);

        $this->model->execute($ids);
    }

    public function testExecuteList()
    {
        $ids = [1, 2, 3];
        $this->productFlatIndexerRows->expects($this->any())->method('execute')->with($ids);

        $result = $this->model->executeList($ids);
        $this->assertNull($result);
    }

    public function testExecuteFull()
    {
        $this->productFlatIndexerFull->expects($this->any())->method('execute');

        $this->cacheContextMock->expects($this->once())
            ->method('registerTags')
            ->with(
                [
                    Category::CACHE_TAG,
                    Product::CACHE_TAG
                ]
            );

        $this->model->executeFull();
    }

    public function testExecuteRow()
    {
        $except = 5;
        $this->productFlatIndexerRow->expects($this->any())->method('execute')->with($except);

        $result = $this->model->executeRow($except);
        $this->assertNull($result);
    }
}
