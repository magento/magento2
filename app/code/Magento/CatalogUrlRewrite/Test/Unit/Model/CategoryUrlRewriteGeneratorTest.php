<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryUrlRewriteGeneratorTest extends TestCase
{
    /** @var MockObject */
    private $canonicalUrlRewriteGenerator;

    /** @var MockObject */
    private $currentUrlRewritesRegenerator;

    /** @var MockObject */
    private $childrenUrlRewriteGenerator;

    /** @var CategoryUrlRewriteGenerator */
    private $categoryUrlRewriteGenerator;

    /** @var StoreViewService|MockObject */
    private $storeViewService;

    /** @var Category|MockObject */
    private $category;

    /** @var CategoryRepositoryInterface|MockObject */
    private $categoryRepository;

    /** @var MockObject */
    private $mergeDataProvider;

    /** @var Json|MockObject */
    protected $serializer;

    /**
     * Test method
     */
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
        $this->childrenUrlRewriteGenerator = $this->getMockBuilder(
            ChildrenUrlRewriteGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->storeViewService = $this->getMockBuilder(StoreViewService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->category = $this->createMock(Category::class);
        $this->categoryRepository = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $mergeDataProviderFactory = $this->createPartialMock(
            MergeDataProviderFactory::class,
            ['create']
        );
        $this->mergeDataProvider = new MergeDataProvider();
        $mergeDataProviderFactory->expects($this->once())->method('create')->willReturn($this->mergeDataProvider);

        $this->categoryUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            CategoryUrlRewriteGenerator::class,
            [
                'canonicalUrlRewriteGenerator' => $this->canonicalUrlRewriteGenerator,
                'childrenUrlRewriteGenerator' => $this->childrenUrlRewriteGenerator,
                'currentUrlRewritesRegenerator' => $this->currentUrlRewritesRegenerator,
                'storeViewService' => $this->storeViewService,
                'categoryRepository' => $this->categoryRepository,
                'mergeDataProviderFactory' => $mergeDataProviderFactory
            ]
        );
    }

    /**
     * Test method
     */
    public function testGenerationForGlobalScope()
    {
        $categoryId = 1;
        $this->category->expects($this->any())->method('getStoreId')->willReturn(null);
        $this->category->expects($this->any())->method('getStoreIds')->willReturn([1]);
        $this->storeViewService->expects($this->once())->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->willReturn(false);
        $canonical = new UrlRewrite([], $this->serializer);
        $canonical->setRequestPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->willReturn(['category-1' => $canonical]);
        $children1 = new UrlRewrite([], $this->serializer);
        $children1->setRequestPath('category-2')
            ->setStoreId(2);
        $children2 = new UrlRewrite([], $this->serializer);
        $children2->setRequestPath('category-22')
            ->setStoreId(2);
        $this->childrenUrlRewriteGenerator->expects($this->any())->method('generate')
            ->with(1, $this->category, $categoryId)
            ->willReturn(['category-2' => $children1, 'category-1' => $children2]);
        $current = new UrlRewrite([], $this->serializer);
        $current->setRequestPath('category-3')
            ->setStoreId(3);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->with(1, $this->category, $categoryId)
            ->willReturn(['category-3' => $current]);
        $categoryForSpecificStore = $this->getMockBuilder(Category::class)
            ->addMethods(['getUrlPath'])
            ->onlyMethods(['getUrlKey'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryRepository->expects($this->once())->method('get')->willReturn($categoryForSpecificStore);

        $this->assertEquals(
            [
                'category-1_1' => $canonical,
                'category-2_2' => $children1,
                'category-22_2' => $children2,
                'category-3_3' => $current
            ],
            $this->categoryUrlRewriteGenerator->generate($this->category, false, $categoryId)
        );
        $this->assertEquals(0, $this->category->getStoreId(), 'Store ID should not have been modified');
    }

    /**
     * Test method
     */
    public function testGenerationForSpecificStore()
    {
        $this->category->expects($this->any())->method('getStoreId')->willReturn(1);
        $this->category->expects($this->never())->method('getStoreIds');
        $canonical = new UrlRewrite([], $this->serializer);
        $canonical->setRequestPath('category-1')
            ->setStoreId(1);
        $this->canonicalUrlRewriteGenerator->expects($this->any())->method('generate')
            ->willReturn([$canonical]);
        $this->childrenUrlRewriteGenerator->expects($this->any())->method('generate')
            ->willReturn([]);
        $this->currentUrlRewritesRegenerator->expects($this->any())->method('generate')
            ->willReturn([]);

        $this->assertEquals(
            ['category-1_1' => $canonical],
            $this->categoryUrlRewriteGenerator->generate($this->category, 1)
        );
    }

    /**
     * Test method
     */
    public function testSkipGenerationForGlobalScope()
    {
        $this->category->expects($this->any())->method('getStoreIds')->willReturn([1, 2]);
        $this->storeViewService->expects($this->exactly(2))->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->willReturn(true);

        $this->assertEquals([], $this->categoryUrlRewriteGenerator->generate($this->category));
    }

    /**
     * Test method
     */
    public function testSkipGenerationForGlobalScopeWithCategory()
    {
        $this->category->expects($this->any())->method('getStoreIds')->willReturn([1, 2]);
        $this->category->expects($this->any())->method('getEntityId')->willReturn(1);
        $this->category->expects($this->any())->method('getStoreId')->willReturn(false);
        $this->storeViewService->expects($this->exactly(2))->method('doesEntityHaveOverriddenUrlKeyForStore')
            ->willReturn(true);

        $this->assertEquals([], $this->categoryUrlRewriteGenerator->generate($this->category, false, 1));
    }
}
