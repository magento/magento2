<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlRewriteHandlerTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var UrlRewriteHandler
     */
    protected $urlRewriteHandler;

    /**
     * @var ChildrenCategoriesProvider|MockObject
     */
    protected $childrenCategoriesProviderMock;

    /**
     * @var CategoryUrlRewriteGenerator|MockObject
     */
    protected $categoryUrlRewriteGeneratorMock;

    /**
     * @var ProductUrlRewriteGenerator|MockObject
     */
    protected $productUrlRewriteGeneratorMock;

    /**
     * @var UrlPersistInterface|MockObject
     */
    protected $urlPersistMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var CategoryProductUrlPathGenerator|MockObject
     */
    private $categoryBasedProductRewriteGeneratorMock;

    /**
     * @var MergeDataProviderFactory|MockObject
     */
    private $mergeDataProviderFactoryMock;

    /**
     * @var MergeDataProvider|MockObject
     */
    private $mergeDataProviderMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var ProductScopeRewriteGenerator
     */
    private $productScopeRewriteGeneratorMock;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->childrenCategoriesProviderMock = $this->createMock(ChildrenCategoriesProvider::class);
        $this->categoryUrlRewriteGeneratorMock = $this->createMock(CategoryUrlRewriteGenerator::class);
        $this->productUrlRewriteGeneratorMock = $this->createMock(ProductUrlRewriteGenerator::class);
        $this->urlPersistMock = $this->createMock(UrlPersistInterface::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->mergeDataProviderFactoryMock = $this->createPartialMock(
            MergeDataProviderFactory::class,
            ['create']
        );
        $this->mergeDataProviderMock = $this->createMock(MergeDataProvider::class);
        $this->categoryBasedProductRewriteGeneratorMock = $this->createMock(CategoryProductUrlPathGenerator::class);
        $this->mergeDataProviderFactoryMock->method('create')
            ->willReturn($this->mergeDataProviderMock);
        $this->serializerMock = $this->createMock(Json::class);
        $this->productScopeRewriteGeneratorMock = $this->createMock(ProductScopeRewriteGenerator::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->urlRewriteHandler = new UrlRewriteHandler(
            $this->childrenCategoriesProviderMock,
            $this->categoryUrlRewriteGeneratorMock,
            $this->productUrlRewriteGeneratorMock,
            $this->urlPersistMock,
            $this->collectionFactoryMock,
            $this->categoryBasedProductRewriteGeneratorMock,
            $this->mergeDataProviderFactoryMock,
            $this->serializerMock,
            $this->productScopeRewriteGeneratorMock,
            $this->scopeConfigMock
        );
    }

    /**
     * @test
     */
    public function testGenerateProductUrlRewrites()
    {
        /* @var \Magento\Catalog\Model\Category|MockObject $category */
        $category = $this->createPartialMockWithReflection(
            Category::class,
            ['getChangedProductIds', 'getEntityId', 'getStoreId', 'getData']
        );
        $category->method('getEntityId')
            ->willReturn(2);
        $category->method('getStoreId')
            ->willReturn(1);
        $category->method('getData')
            ->willReturnCallback(function ($arg1) {
                static $callCount = 0;
                $callCount++;
                switch ($callCount) {
                    case 1:
                        if ($arg1 == 'save_rewrites_history') {
                            return true;
                        }
                        break;
                    case 2:
                        if ($arg1 == 'initial_setup_flag') {
                            return null;
                        }
                        break;
                }
            });

        /* @var \Magento\Catalog\Model\Category|MockObject $childCategory1 */
        $childCategory1 = $this->createPartialMock(
            Category::class,
            ['getEntityId']
        );
        $childCategory1->method('getEntityId')
            ->willReturn(100);

        /* @var \Magento\Catalog\Model\Category|MockObject $childCategory2 */
        $childCategory2 = $this->createPartialMock(
            Category::class,
            ['getEntityId']
        );
        $childCategory2->method('getEntityId')
            ->willReturn(200);

        $this->childrenCategoriesProviderMock->expects($this->once())
            ->method('getChildren')
            ->with($category, true)
            ->willReturn([$childCategory1, $childCategory2]);

        /** @var Collection|MockObject $productCollection */
        $productCollection = $this->createMock(Collection::class);
        $productCollection->method('addCategoriesFilter')
            ->willReturnSelf();
        $productCollection->method('addIdFilter')
            ->willReturnSelf();
        $productCollection->method('setStoreId')->willReturnSelf();
        $productCollection->method('addStoreFilter')->willReturnSelf();
        $productCollection->method('addAttributeToSelect')->willReturnSelf();
        $iterator = new \ArrayIterator([]);
        $productCollection->method('getIterator')->willReturn($iterator);

        $this->collectionFactoryMock->method('create')->willReturn($productCollection);

        $this->mergeDataProviderMock->method('getData')->willReturn([1, 2]);

        $this->urlRewriteHandler->generateProductUrlRewrites($category);
    }

    public function testDeleteCategoryRewritesForChildren()
    {
        $category = $this->createMock(Category::class);
        $category->expects($this->once())
            ->method('getId')
            ->willReturn(2);

        $this->childrenCategoriesProviderMock->expects($this->once())
            ->method('getChildrenIds')
            ->with($category, true)
            ->willReturn([3, 4]);

        $this->serializerMock->expects($this->exactly(3))
            ->method('serialize');

        $this->urlRewriteHandler->deleteCategoryRewritesForChildren($category);
    }
}
