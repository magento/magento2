<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Category as CategoryPlugin;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    /**
     * @var MockObject|IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var MockObject|CategoryResourceModel
     */
    protected $categoryResourceMock;

    /**
     * @var MockObject|CategoryModel
     */
    protected $categoryMock;

    /**
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var CategoryPlugin
     */
    protected $model;

    protected function setUp(): void
    {
        $this->categoryMock = $this->createMock(CategoryModel::class);

        $this->categoryResourceMock = $this->createMock(CategoryResourceModel::class);

        $connection = $this->createMock(AdapterInterface::class);
        $this->categoryResourceMock->method('getConnection')->willReturn($connection);

        $this->indexerMock = $this->createStub(IndexerInterface::class);

        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );

        $this->proceed = function () {
            return $this->categoryResourceMock;
        };

        $this->model = (new ObjectManager($this))->getObject(
            CategoryPlugin::class,
            ['indexerRegistry' => $this->indexerRegistryMock]
        );
    }

    public function testAfterSaveNonScheduled()
    {
        $this->categoryResourceMock->expects($this->once())->method('addCommitCallback');
        $this->assertEquals(
            $this->categoryResourceMock,
            $this->model->aroundSave($this->categoryResourceMock, $this->proceed, $this->categoryMock)
        );
    }

    public function testAfterSaveScheduled()
    {
        $this->categoryResourceMock->expects($this->once())->method('addCommitCallback');
        $this->assertEquals(
            $this->categoryResourceMock,
            $this->model->aroundSave($this->categoryResourceMock, $this->proceed, $this->categoryMock)
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
