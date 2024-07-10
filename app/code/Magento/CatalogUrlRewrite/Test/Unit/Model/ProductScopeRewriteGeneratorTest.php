<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory;
use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductScopeRewriteGeneratorTest extends TestCase
{
    /** @var MockObject */
    private $canonicalUrlRewriteGenerator;

    /** @var MockObject */
    private $currentUrlRewritesRegenerator;

    /** @var MockObject */
    private $categoriesUrlRewriteGenerator;

    /** @var MockObject */
    private $anchorUrlRewriteGenerator;

    /** @var StoreViewService|MockObject */
    private $storeViewService;

    /** @var ObjectRegistryFactory|MockObject */
    private $objectRegistryFactory;

    /** @var  StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var  ProductScopeRewriteGenerator */
    private $productScopeGenerator;

    /** @var MockObject */
    private $mergeDataProvider;

    /** @var Json|MockObject */
    private $serializer;

    /** @var Category|MockObject */
    private $categoryMock;

    /** @var ScopeConfigInterface|MockObject */
    private $configMock;

    /** @var CategoryRepositoryInterface|MockObject */
    private $categoryRepositoryMock;

    /** @var ProductRepositoryInterface|MockObject */
    private $productRepositoryMock;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(Json::class);
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->currentUrlRewritesRegenerator = $this->getMockBuilder(
            CurrentUrlRewritesRegenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->canonicalUrlRewriteGenerator = $this->getMockBuilder(
            CanonicalUrlRewriteGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->categoriesUrlRewriteGenerator = $this->getMockBuilder(
            CategoriesUrlRewriteGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->anchorUrlRewriteGenerator = $this->getMockBuilder(
            AnchorUrlRewriteGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->objectRegistryFactory = $this->getMockBuilder(
            ObjectRegistryFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $this->storeViewService = $this->getMockBuilder(StoreViewService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeRootCategoryId = 2;
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getRootCategoryId')->willReturn($storeRootCategoryId);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $mergeDataProviderFactory = $this->createPartialMock(
            MergeDataProviderFactory::class,
            ['create']
        );
        $this->mergeDataProvider = new MergeDataProvider();
        $mergeDataProviderFactory->expects($this->once())->method('create')->willReturn($this->mergeDataProvider);
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);

        $this->productScopeGenerator = (new ObjectManager($this))->getObject(
            ProductScopeRewriteGenerator::class,
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'categoriesUrlRewriteGenerator' => $this->categoriesUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'anchorUrlRewriteGenerator' => $this->anchorUrlRewriteGenerator,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'storeViewService' => $this->storeViewService,
                'storeManager' => $this->storeManager,
                'mergeDataProviderFactory' => $mergeDataProviderFactory,
                'config' => $this->configMock,
                'categoryRepository' => $this->categoryRepositoryMock,
                'productRepository' =>$this->productRepositoryMock
            ]
        );
        $this->categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGenerationForGlobalScope()
    {
        $this->configMock->expects($this->any())->method('getValue')
            ->with('catalog/seo/generate_category_product_rewrites')
            ->willReturn('1');
        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getStoreId')->willReturn(null);
        $product->expects($this->any())->method('getStoreIds')->willReturn([1]);
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getStoreGroupId')->willReturn(1);
        $this->storeManager->expects($this->any())->method('getStores')->willReturn([$store]);
        $this->storeViewService->expects($this->once())->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->willReturn(true);
        $this->initObjectRegistryFactory([]);
        $canonical = new UrlRewrite([], $this->serializer);
        $canonical->setRequestPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->willReturn([$canonical]);
        $categories = new UrlRewrite([], $this->serializer);
        $categories->setRequestPath('category-2')
            ->setStoreId(2);
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->willReturn([$categories]);
        $current = new UrlRewrite([], $this->serializer);
        $current->setRequestPath('category-3')
            ->setStoreId(3);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->willReturn([$current]);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generateAnchor')
            ->willReturn([$current]);
        $anchorCategories = new UrlRewrite([], $this->serializer);
        $anchorCategories->setRequestPath('category-4')
            ->setStoreId(4);
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->willReturn([$anchorCategories]);
        $this->productRepositoryMock->expects($this->once())->method('getById')->willReturn($product);

        $this->assertEquals(
            [
                'category-1_1' => $canonical,
                'category-2_2' => $categories,
                'category-3_3' => $current,
                'category-4_4' => $anchorCategories
            ],
            $this->productScopeGenerator->generateForGlobalScope([$this->categoryMock], $product, 1)
        );
    }

    public function testGenerationForSpecificStore()
    {
        $storeRootCategoryId = 2;
        $category_id = 4;
        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getStoreId')->willReturn(1);
        $product->expects($this->never())->method('getStoreIds');
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getStoreGroupId')->willReturn(1);
        $this->storeManager->expects($this->any())->method('getStores')->willReturn([$store]);
        $this->categoryMock->expects($this->any())->method('getParentIds')
            ->willReturn(['root-id', $storeRootCategoryId]);
        $this->categoryMock->expects($this->any())->method('getId')->willReturn($category_id);
        $this->initObjectRegistryFactory([$this->categoryMock]);
        $canonical = new UrlRewrite([], $this->serializer);
        $canonical->setRequestPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->willReturn([$canonical]);
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->willReturn([]);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->willReturn([]);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generateAnchor')
            ->willReturn([]);
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->willReturn([]);

        $this->categoryRepositoryMock->expects($this->once())->method('get')->willReturn($this->categoryMock);

        $this->assertEquals(
            ['category-1_1' => $canonical],
            $this->productScopeGenerator->generateForSpecificStoreView(1, [$this->categoryMock], $product, 1)
        );
    }

    /**
     * @param array $entities
     */
    protected function initObjectRegistryFactory($entities)
    {
        $objectRegistry = $this->getMockBuilder(ObjectRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectRegistryFactory->expects($this->any())->method('create')
            ->with(['entities' => $entities])
            ->willReturn($objectRegistry);
    }

    /**
     * Test the possibility of url rewrite generation.
     *
     * @param array $parentIds
     * @param bool $expectedResult
     * @dataProvider isCategoryProperForGeneratingDataProvider
     */
    public function testIsCategoryProperForGenerating($parentIds, $expectedResult)
    {
        $storeId = 1;
        $this->categoryMock->expects(self::any())->method('getParentIds')->willReturn($parentIds);
        $result = $this->productScopeGenerator->isCategoryProperForGenerating(
            $this->categoryMock,
            $storeId
        );
        self::assertEquals(
            $expectedResult,
            $result
        );
    }

    /**
     * Data provider for testIsCategoryProperForGenerating.
     *
     * @return array
     */
    public function isCategoryProperForGeneratingDataProvider()
    {
        return [
            [['0'], false],
            [['1'], false],
            [['1', '2'], true],
            [['1', '3'], false],
        ];
    }
}
