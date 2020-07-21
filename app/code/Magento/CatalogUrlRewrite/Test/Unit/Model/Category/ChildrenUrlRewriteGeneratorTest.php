<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory;
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
class ChildrenUrlRewriteGeneratorTest extends TestCase
{
    /** @var ChildrenUrlRewriteGenerator */
    private $childrenUrlRewriteGenerator;

    /** @var MockObject */
    private $category;

    /** @var MockObject */
    private $childrenCategoriesProvider;

    /** @var MockObject */
    private $categoryUrlRewriteGeneratorFactory;

    /** @var MockObject */
    private $categoryUrlRewriteGenerator;

    /** @var MockObject */
    private $mergeDataProvider;

    /** @var MockObject */
    private $serializerMock;

    /** @var MockObject */
    private $categoryRepository;

    protected function setUp(): void
    {
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->childrenCategoriesProvider = $this->getMockBuilder(
            ChildrenCategoriesProvider::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryUrlRewriteGeneratorFactory = $this->getMockBuilder(
            CategoryUrlRewriteGeneratorFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $this->categoryUrlRewriteGenerator = $this->getMockBuilder(
            CategoryUrlRewriteGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->categoryRepository = $this->getMockBuilder(
            CategoryRepository::class
        )->disableOriginalConstructor()
            ->getMock();
        $mergeDataProviderFactory = $this->createPartialMock(
            MergeDataProviderFactory::class,
            ['create']
        );
        $this->mergeDataProvider = new MergeDataProvider();
        $mergeDataProviderFactory->expects($this->once())->method('create')->willReturn($this->mergeDataProvider);

        $this->childrenUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            ChildrenUrlRewriteGenerator::class,
            [
                'childrenCategoriesProvider' => $this->childrenCategoriesProvider,
                'categoryUrlRewriteGeneratorFactory' => $this->categoryUrlRewriteGeneratorFactory,
                'mergeDataProviderFactory' => $mergeDataProviderFactory,
                'categoryRepository' => $this->categoryRepository
            ]
        );
    }

    public function testNoChildrenCategories()
    {
        $this->childrenCategoriesProvider->expects($this->once())->method('getChildrenIds')->with($this->category, true)
            ->willReturn([]);

        $this->assertEquals([], $this->childrenUrlRewriteGenerator->generate('store_id', $this->category));
    }

    public function testGenerate()
    {
        $storeId = 'store_id';
        $saveRewritesHistory = 'flag';
        $childId = 2;

        $childCategory = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $childCategory->expects($this->once())->method('setData')
            ->with('save_rewrites_history', $saveRewritesHistory);
        $this->childrenCategoriesProvider->expects($this->once())->method('getChildrenIds')->with($this->category, true)
            ->willReturn([$childId]);
        $this->categoryRepository->expects($this->once())->method('get')
            ->with($childId, $storeId)->willReturn($childCategory);
        $this->category->expects($this->any())->method('getData')->with('save_rewrites_history')
            ->willReturn($saveRewritesHistory);
        $this->categoryUrlRewriteGeneratorFactory->expects($this->once())->method('create')
            ->willReturn($this->categoryUrlRewriteGenerator);
        $url1 = new UrlRewrite([], $this->serializerMock);
        $url1->setRequestPath('category-1')
            ->setStoreId(1);
        $url2 = new UrlRewrite([], $this->serializerMock);
        $url2->setRequestPath('category-2')
            ->setStoreId(2);
        $url3 = new UrlRewrite([], $this->serializerMock);
        $url3->setRequestPath('category-1')
            ->setStoreId(1);
        $this->categoryUrlRewriteGenerator->expects($this->once())->method('generate')
            ->with($childCategory, false, 1)
            ->willReturn([$url1, $url2, $url3]);

        $this->assertEquals(
            ['category-1_1'  => $url1, 'category-2_2' => $url2],
            $this->childrenUrlRewriteGenerator->generate($storeId, $this->category, 1)
        );
    }
}
