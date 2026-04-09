<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $this->productMock = $this->createMock(ProductModel::class);

        $this->subjectMock = $this->createMock(ProductResourceModel::class);

        $connection = $this->createMock(AdapterInterface::class);
        $this->subjectMock->method('getConnection')->willReturn($connection);

        $this->indexerMock = $this->createStub(IndexerInterface::class);

        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );

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
