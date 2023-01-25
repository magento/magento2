<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin\Store;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product as Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogUrlRewrite\Model\Category\Plugin\Store\Group as GroupPlugin;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GroupPlugin
     */
    private $plugin;

    /**
     * @var AbstractModel|MockObject
     */
    private $abstractModelMock;

    /**
     * @var Group|MockObject
     */
    private $subjectMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var CategoryFactory|MockObject
     */
    private $categoryFactoryMock;

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
     * @var ProductFactory|MockObject
     */
    private $productFactoryMock;

    /**
     * @var ProductUrlRewriteGenerator|MockObject
     */
    private $productUrlRewriteGeneratorMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->abstractModelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['isObjectNew', 'dataHasChangedFor', 'getStoreIds'])
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['reinitStores'])
            ->getMockForAbstractClass();
        $this->categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCategories'])
            ->getMock();
        $this->categoryFactoryMock = $this->getMockBuilder(CategoryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productCollectionMock = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addCategoryIds', 'addAttributeToSelect', 'addWebsiteFilter', 'getIterator'])
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollection'])
            ->getMock();
        $this->productUrlRewriteGeneratorMock = $this->getMockBuilder(ProductUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $this->plugin = $this->objectManager->getObject(
            GroupPlugin::class,
            [
                'storeManager' => $this->storeManagerMock,
                'categoryFactory' => $this->categoryFactoryMock,
                'productFactory' => $this->productFactoryMock,
                'productUrlRewriteGenerator' => $this->productUrlRewriteGeneratorMock
            ]
        );
    }

    public function testAfterSave()
    {
        $this->abstractModelMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(false);
        $this->abstractModelMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn(['1']);
        $this->abstractModelMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('website_id')
            ->willReturn(true);
        $this->storeManagerMock->expects($this->once())
            ->method('reinitStores');
        $this->categoryMock->expects($this->once())
            ->method('getCategories')
            ->willReturn([]);
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
            ->method('addWebsiteFilter')
            ->willReturn($this->productCollectionMock);
        $arrayIteratorMock = new \ArrayIterator([$this->productMock]);
        $this->productCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn($arrayIteratorMock);
        $this->productUrlRewriteGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($this->productMock)
            ->willReturn([]);

        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterSave($this->subjectMock, $this->subjectMock, $this->abstractModelMock)
        );
    }

    public function testAfterSaveWithNoStoresAssigned()
    {
        $this->abstractModelMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(false);
        $this->abstractModelMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([]);
        $this->abstractModelMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('website_id')
            ->willReturn(true);
        $this->storeManagerMock->expects($this->never())->method('reinitStores');
        $this->categoryMock->expects($this->never())->method('getCategories');
        $this->categoryFactoryMock->expects($this->never())->method('create');
        $this->productFactoryMock->expects($this->never())->method('create');
        $this->productMock->expects($this->never())->method('getCollection');
        $this->productCollectionMock->expects($this->never())->method('addCategoryIds');
        $this->productCollectionMock->expects($this->never())            ->method('addAttributeToSelect');
        $this->productCollectionMock->expects($this->never())->method('addWebsiteFilter');
        $this->productCollectionMock->expects($this->never())->method('getIterator');
        $this->productUrlRewriteGeneratorMock->expects($this->never())->method('generate');

        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterSave($this->subjectMock, $this->subjectMock, $this->abstractModelMock)
        );
    }
}
