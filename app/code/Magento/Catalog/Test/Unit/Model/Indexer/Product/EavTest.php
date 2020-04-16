<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Product\Eav;
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Full;
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Row;
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows;
use Magento\Catalog\Model\Product;
use Magento\Framework\Indexer\CacheContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EavTest extends TestCase
{
    /**
     * @var Row|MockObject
     */
    protected $_productEavIndexerRow;

    /**
     * @var Rows|MockObject
     */
    protected $_productEavIndexerRows;

    /**
     * @var Full|MockObject
     */
    protected $_productEavIndexerFull;

    /**
     * @var CacheContext|MockObject
     */
    protected $cacheContextMock;
    /**
     * @var Eav
     */
    private $model;

    protected function setUp(): void
    {
        $this->_productEavIndexerRow = $this->getMockBuilder(
            Row::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->_productEavIndexerRows = $this->getMockBuilder(
            Rows::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->_productEavIndexerFull = $this->getMockBuilder(
            Full::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Eav(
            $this->_productEavIndexerRow,
            $this->_productEavIndexerRows,
            $this->_productEavIndexerFull
        );

        $this->cacheContextMock = $this->createMock(CacheContext::class);

        $cacheContextProperty = new \ReflectionProperty(
            Eav::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->model, $this->cacheContextMock);
    }

    public function testExecute()
    {
        $ids = [1, 2, 3];
        $this->_productEavIndexerRow->expects($this->any())
            ->method('execute')
            ->with($ids);

        $this->cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(Product::CACHE_TAG, $ids);

        $this->model->execute($ids);
    }

    public function testExecuteList()
    {
        $ids = [1, 2, 3];
        $this->_productEavIndexerRow->expects($this->any())
            ->method('execute')
            ->with($ids);

        $result = $this->model->executeList($ids);
        $this->assertNull($result);
    }

    public function testExecuteFull()
    {
        $this->_productEavIndexerFull->expects($this->once())
            ->method('execute');

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
        $id = 11;
        $this->_productEavIndexerRow->expects($this->once())
            ->method('execute')
            ->with($id);

        $this->model->executeRow($id);
    }
}
