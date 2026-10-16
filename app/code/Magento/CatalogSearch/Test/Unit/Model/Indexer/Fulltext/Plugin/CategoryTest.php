<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Category as CategoryPlugin;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @param string $changedField
     */
    #[DataProvider('changedFieldDataProvider')]
    public function testReindexesAllCategoryProductsWhenFlagChanged(string $changedField): void
    {
        $category = $this->createPartialMock(CategoryModel::class, ['dataHasChangedFor', 'getProductCollection']);
        $category->setData('affected_product_ids', [5]);
        $category->method('dataHasChangedFor')
            ->willReturnCallback(fn (string $field): bool => $field === $changedField);
        $productCollection = $this->createStub(ProductCollection::class);
        $productCollection->method('getAllIds')->willReturn([1, 2, 3]);
        $category->method('getProductCollection')->willReturn($productCollection);

        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->method('isScheduled')->willReturn(false);
        $indexer->expects($this->once())->method('reindexList')->with([1, 2, 3]);
        $this->indexerRegistryMock->method('get')->with(Fulltext::INDEXER_ID)->willReturn($indexer);

        $this->saveAndRunCommitCallback($category);
    }

    /**
     * @return array
     */
    public static function changedFieldDataProvider(): array
    {
        return [
            'is_active changed' => ['is_active'],
            'is_anchor changed' => ['is_anchor'],
        ];
    }

    public function testReindexesAffectedProductsWhenFlagsNotChanged(): void
    {
        $category = $this->createPartialMock(CategoryModel::class, ['dataHasChangedFor', 'getProductCollection']);
        $category->setData('affected_product_ids', [5]);
        $category->method('dataHasChangedFor')->willReturn(false);
        $category->expects($this->never())->method('getProductCollection');

        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->method('isScheduled')->willReturn(false);
        $indexer->expects($this->once())->method('reindexList')->with([5]);
        $this->indexerRegistryMock->method('get')->with(Fulltext::INDEXER_ID)->willReturn($indexer);

        $this->saveAndRunCommitCallback($category);
    }

    public function testDoesNotReindexWithoutAffectedProducts(): void
    {
        $category = $this->createPartialMock(CategoryModel::class, ['dataHasChangedFor', 'getProductCollection']);
        $category->method('dataHasChangedFor')->willReturn(false);

        $this->indexerRegistryMock->expects($this->never())->method('get');

        $this->saveAndRunCommitCallback($category);
    }

    /**
     * Save the category through the plugin and run the registered commit callback
     *
     * @param CategoryModel $category
     * @return void
     */
    private function saveAndRunCommitCallback(CategoryModel $category): void
    {
        $commitCallback = null;
        $this->categoryResourceMock->expects($this->once())
            ->method('addCommitCallback')
            ->willReturnCallback(function (callable $callback) use (&$commitCallback) {
                $commitCallback = $callback;
                return $this->categoryResourceMock;
            });

        $this->model->aroundSave($this->categoryResourceMock, $this->proceed, $category);

        $this->assertIsCallable($commitCallback);
        $commitCallback();
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Fulltext::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }
}
