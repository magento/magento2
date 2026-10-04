<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
    use MockCreationTrait;

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
        $this->abstractModelMock = $this->createPartialMockWithReflection(
            AbstractModel::class,
            ['getStoreIds', 'getWebsiteId', 'isObjectNew', 'dataHasChangedFor']
        );
        $this->subjectMock = $this->createMock(Group::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->categoryMock = $this->createPartialMock(
            Category::class,
            ['getCategories']
        );
        $this->categoryFactoryMock = $this->createPartialMock(
            CategoryFactory::class,
            ['create']
        );
        $this->productFactoryMock = $this->createPartialMock(
            ProductFactory::class,
            ['create']
        );
        $this->productCollectionMock = $this->createPartialMock(
            ProductCollection::class,
            ['addCategoryIds', 'addAttributeToSelect', 'addWebsiteFilter', 'getIterator']
        );
        $this->productMock = $this->createPartialMock(
            Product::class,
            ['getCollection']
        );
        $this->productUrlRewriteGeneratorMock = $this->createPartialMock(
            ProductUrlRewriteGenerator::class,
            ['generate']
        );
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
        $this->abstractModelMock->method('getStoreIds')
            ->willReturn(['1']);
        $this->abstractModelMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);
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
        $this->abstractModelMock->method('getStoreIds')
            ->willReturn([]);
        $this->abstractModelMock->method('dataHasChangedFor')
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
