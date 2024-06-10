<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin\Store;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogUrlRewrite\Model\Category\Plugin\Store\View as StoreViewPlugin;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    /**
     * @var StoreViewPlugin
     */
    private $plugin;

    /**
     * @var AbstractModel|MockObject
     */
    private $abstractModelMock;

    /**
     * @var StoreResourceModel|MockObject
     */
    private $subjectMock;

    /**
     * @var UrlPersistInterface|MockObject
     */
    private $urlPersistMock;

    /**
     * @var CategoryFactory|MockObject
     */
    private $categoryFactoryMock;

    /**
     * @var ProductFactory|MockObject
     */
    private $productFactoryMock;

    /**
     * @var CategoryUrlRewriteGenerator|MockObject
     */
    private $categoryUrlRewriteGeneratorMock;

    /**
     * @var ProductUrlRewriteGenerator|MockObject
     */
    private $productUrlRewriteGeneratorMock;

    /**
     * @var Category|MockObject
     */
    private $categoryMock;

    /**
     * @var ProductCollection|MockObject
     */
    private $productCollectionMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->abstractModelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isObjectNew'])
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(StoreResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlPersistMock = $this->getMockBuilder(UrlPersistInterface::class)
            ->onlyMethods(['deleteByData'])
            ->getMockForAbstractClass();
        $this->categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCategories'])
            ->getMock();
        $this->categoryFactoryMock = $this->getMockBuilder(CategoryFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->productFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->categoryUrlRewriteGeneratorMock = $this->getMockBuilder(CategoryUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlRewriteGeneratorMock = $this->getMockBuilder(ProductUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generate'])
            ->getMock();
        $this->productCollectionMock = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addCategoryIds', 'addAttributeToSelect', 'getIterator', 'addStoreFilter'])
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollection'])
            ->getMock();
        $this->plugin = new StoreViewPlugin(
            $this->urlPersistMock,
            $this->categoryFactoryMock,
            $this->productFactoryMock,
            $this->categoryUrlRewriteGeneratorMock,
            $this->productUrlRewriteGeneratorMock
        );
    }

    /**
     * Test after save
     *
     * @return void
     */
    public function testAfterSave(): void
    {
        $origStoreMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reflectionStore = new \ReflectionClass($this->plugin);
        $origStore = $reflectionStore->getProperty('origStore');
        $origStore->setAccessible(true);
        $origStore->setValue($this->plugin, $origStoreMock);

        $origStoreMock->expects($this->atLeastOnce())
            ->method('getData')
            ->with('group_id')
            ->willReturn('1');

        $origStoreMock->expects($this->atLeastOnce())
            ->method('isObjectNew')
            ->willReturn(true);

        $this->abstractModelMock->expects($this->any())
            ->method('isObjectNew')
            ->willReturn(true);
        $categoryCollection = $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIterator'])
            ->getMock();
        $categoryCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
        $this->categoryMock->expects($this->once())
            ->method('getCategories')
            ->willReturn($categoryCollection);
        $this->categoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->categoryMock);
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->productCollectionMock);
        $this->productCollectionMock->expects($this->once())
            ->method('addCategoryIds')
            ->willReturn($this->productCollectionMock);
        $this->productCollectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->willReturn($this->productCollectionMock);
        $this->productCollectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->willReturn($this->productCollectionMock);
        $iterator = new \ArrayIterator([$this->productMock]);
        $this->productCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);
        $this->productUrlRewriteGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($this->productMock)
            ->willReturn([]);

        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterSave($this->subjectMock, $this->subjectMock)
        );
    }

    public function testAfterSaveWhenNoGroupId()
    {
        $origStoreMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reflectionStore = new \ReflectionClass($this->plugin);
        $origStore = $reflectionStore->getProperty('origStore');
        $origStore->setAccessible(true);
        $origStore->setValue($this->plugin, $origStoreMock);

        $origStoreMock->expects($this->atLeastOnce())
            ->method('getData')
            ->with('group_id')
            ->willReturn(null);

        $origStoreMock->expects($this->any())
            ->method('isObjectNew')
            ->willReturn(true);

        $this->abstractModelMock->expects($this->any())
            ->method('isObjectNew')
            ->willReturn(true);
        $categoryCollection = $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIterator'])
            ->getMock();
        $categoryCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
        $this->categoryMock->expects($this->never())
            ->method('getCategories');
        $this->categoryFactoryMock->expects($this->never())->method('create');
        $this->productFactoryMock->expects($this->never())->method('create');
        $this->productMock->expects($this->never())->method('getCollection');
        $this->productCollectionMock->expects($this->never())->method('addCategoryIds');
        $this->productCollectionMock->expects($this->never())->method('addAttributeToSelect');
        $this->productCollectionMock->expects($this->never())->method('addStoreFilter');

        $this->productCollectionMock->expects($this->never())->method('getIterator');
        $this->productUrlRewriteGeneratorMock->expects($this->never())->method('generate');

        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterSave($this->subjectMock, $this->subjectMock)
        );
    }

    /**
     * Test after delete
     *
     * @return void
     */
    public function testAfterDelete(): void
    {
        $this->urlPersistMock->expects($this->once())
            ->method('deleteByData');
        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterDelete($this->subjectMock, $this->subjectMock, $this->abstractModelMock)
        );
    }
}
