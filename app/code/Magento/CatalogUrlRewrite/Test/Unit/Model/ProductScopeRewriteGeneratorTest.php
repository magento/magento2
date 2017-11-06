<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductScopeRewriteGeneratorTest
 * @package Magento\CatalogUrlRewrite\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductScopeRewriteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $canonicalUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $currentUrlRewritesRegenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $categoriesUrlRewriteGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $anchorUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService|\PHPUnit_Framework_MockObject_MockObject */
    private $storeViewService;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $objectRegistryFactory;

    /** @var  StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $storeManager;

    /** @var  ProductScopeRewriteGenerator */
    private $productScopeGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $mergeDataProvider;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject */
    private $serializer;

    /** @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject */
    private $categoryMock;

    public function setUp()
    {
        $this->serializer = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);
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
            \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->categoriesUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->anchorUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->objectRegistryFactory = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->storeViewService = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Service\V1\StoreViewService::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $storeRootCategoryId = 2;
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getRootCategoryId')->will($this->returnValue($storeRootCategoryId));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $mergeDataProviderFactory = $this->createPartialMock(
            \Magento\UrlRewrite\Model\MergeDataProviderFactory::class,
            ['create']
        );
        $this->mergeDataProvider = new \Magento\UrlRewrite\Model\MergeDataProvider();
        $mergeDataProviderFactory->expects($this->once())->method('create')->willReturn($this->mergeDataProvider);

        $this->productScopeGenerator = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator::class,
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'categoriesUrlRewriteGenerator' => $this->categoriesUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'anchorUrlRewriteGenerator' => $this->anchorUrlRewriteGenerator,
                'objectRegistryFactory' => $this->objectRegistryFactory,
                'storeViewService' => $this->storeViewService,
                'storeManager' => $this->storeManager,
                'mergeDataProviderFactory' => $mergeDataProviderFactory
            ]
        );
        $this->categoryMock = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
    }

    public function testGenerationForGlobalScope()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue(null));
        $product->expects($this->any())->method('getStoreIds')->will($this->returnValue([1]));
        $this->storeViewService->expects($this->once())->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(false));
        $this->initObjectRegistryFactory([]);
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite([], $this->serializer);
        $canonical->setRequestPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $categories = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite([], $this->serializer);
        $categories->setRequestPath('category-2')
            ->setStoreId(2);
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$categories]));
        $current = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite([], $this->serializer);
        $current->setRequestPath('category-3')
            ->setStoreId(3);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$current]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generateAnchor')
            ->will($this->returnValue([$current]));
        $anchorCategories = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite([], $this->serializer);
        $anchorCategories->setRequestPath('category-4')
            ->setStoreId(4);
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$anchorCategories]));

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
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $product->expects($this->never())->method('getStoreIds');
        $this->categoryMock->expects($this->any())->method('getParentIds')
            ->will($this->returnValue(['root-id', $storeRootCategoryId]));
        $this->categoryMock->expects($this->any())->method('getId')->will($this->returnValue($category_id));
        $this->initObjectRegistryFactory([$this->categoryMock]);
        $canonical = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite([], $this->serializer);
        $canonical->setRequestPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([$canonical]));
        $this->categoriesUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generateAnchor')
            ->will($this->returnValue([]));
        $this->anchorUrlRewriteGenerator->expects($this->any())->method('generate')
            ->will($this->returnValue([]));

        $this->assertEquals(
            ['category-1_1' => $canonical],
            $this->productScopeGenerator->generateForSpecificStoreView(1, [$this->categoryMock], $product, 1)
        );
    }

    /**
     * Test method
     */
    public function testSkipGenerationForGlobalScope()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->any())->method('getStoreIds')->will($this->returnValue([1, 2]));
        $this->storeViewService->expects($this->exactly(2))->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->will($this->returnValue(true));

        $this->assertEquals([], $this->productScopeGenerator->generateForGlobalScope([], $product, 1));
    }

    /**
     * @param array $entities
     */
    protected function initObjectRegistryFactory($entities)
    {
        $objectRegistry = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Model\ObjectRegistry::class)
            ->disableOriginalConstructor()->getMock();
        $this->objectRegistryFactory->expects($this->any())->method('create')
            ->with(['entities' => $entities])
            ->will($this->returnValue($objectRegistry));
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
