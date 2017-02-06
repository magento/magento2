<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productFlatIndexerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productFlatIndexerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productFlatIndexerFull;

    /**
     * @var \Magento\Framework\Indexer\CacheContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheContextMock;

    protected function setUp()
    {
        $this->productFlatIndexerRow = $this->getMockBuilder(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFlatIndexerRows = $this->getMockBuilder(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFlatIndexerFull = $this->getMockBuilder(
            \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Indexer\Product\Flat::class,
            [
                'productFlatIndexerRow' => $this->productFlatIndexerRow,
                'productFlatIndexerRows' => $this->productFlatIndexerRows,
                'productFlatIndexerFull' => $this->productFlatIndexerFull
            ]
        );

        $this->cacheContextMock = $this->getMock(\Magento\Framework\Indexer\CacheContext::class, [], [], '', false);

        $cacheContextProperty = new \ReflectionProperty(
            \Magento\Catalog\Model\Indexer\Product\Flat::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->model, $this->cacheContextMock);
    }

    public function testExecute()
    {
        $ids = [1, 2, 3];
        $this->productFlatIndexerRows->expects($this->any())->method('execute')->with($this->equalTo($ids));

        $this->cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);

        $this->model->execute($ids);
    }

    public function testExecuteList()
    {
        $ids = [1, 2, 3];
        $this->productFlatIndexerRows->expects($this->any())->method('execute')->with($this->equalTo($ids));

        $this->model->executeList($ids);
    }

    public function testExecuteFull()
    {
        $this->productFlatIndexerFull->expects($this->any())->method('execute');

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

    public function testExecuteRow()
    {
        $except = 5;
        $this->productFlatIndexerRow->expects($this->any())->method('execute')->with($this->equalTo($except));

        $this->model->executeRow($except);
    }
}
