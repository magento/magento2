<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ChildrenUrlRewriteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator */
    private $childrenUrlRewriteGenerator;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $category;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $childrenCategoriesProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $categoryUrlRewriteGeneratorFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $categoryUrlRewriteGenerator;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $mergeDataProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $serializerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $categoryRepository;

    protected function setUp(): void
    {
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->childrenCategoriesProvider = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider::class
        )->disableOriginalConstructor()->getMock();
        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()->getMock();
        $this->categoryUrlRewriteGeneratorFactory = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->categoryUrlRewriteGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->categoryRepository = $this->getMockBuilder(
            \Magento\Catalog\Model\CategoryRepository::class
        )->disableOriginalConstructor()->getMock();
        $mergeDataProviderFactory = $this->createPartialMock(
            \Magento\UrlRewrite\Model\MergeDataProviderFactory::class,
            ['create']
        );
        $this->mergeDataProvider = new \Magento\UrlRewrite\Model\MergeDataProvider();
        $mergeDataProviderFactory->expects($this->once())->method('create')->willReturn($this->mergeDataProvider);

        $this->childrenUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator::class,
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

        $childCategory = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()->getMock();
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
        $url1 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite([], $this->serializerMock);
        $url1->setRequestPath('category-1')
            ->setStoreId(1);
        $url2 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite([], $this->serializerMock);
        $url2->setRequestPath('category-2')
            ->setStoreId(2);
        $url3 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite([], $this->serializerMock);
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
