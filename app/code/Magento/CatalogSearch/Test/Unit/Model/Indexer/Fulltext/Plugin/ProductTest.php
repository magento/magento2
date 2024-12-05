<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Product;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var MockObject|IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var MockObject|ProductResourceModel
     */
    protected $subjectMock;

    /**
     * @var MockObject|ProductModel
     */
    protected $productMock;

    /**
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var Product
     */
    protected $model;

    protected function setUp(): void
    {
        $this->productMock = $this->getMockBuilder(ProductModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(ProductResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->subjectMock->method('getConnection')->willReturn($connection);

        $this->indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getId', 'getState'])
            ->getMockForAbstractClass();
        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $this->proceed = function () {
            return $this->subjectMock;
        };

        $this->model = (new ObjectManager($this))->getObject(
            Product::class,
            ['indexerRegistry' => $this->indexerRegistryMock]
        );
    }

    public function testAfterSaveNonScheduled()
    {
        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $this->proceed, $this->productMock)
        );
    }

    public function testAfterSaveScheduled()
    {
        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $this->proceed, $this->productMock)
        );
    }

    public function testAfterDeleteNonScheduled()
    {
        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundDelete($this->subjectMock, $this->proceed, $this->productMock)
        );
    }

    public function testAfterDeleteScheduled()
    {
        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundDelete($this->subjectMock, $this->proceed, $this->productMock)
        );
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Fulltext::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }
}
