<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\UpdateUrlPath;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\UpdateUrlPath class.
 */
class UpdateUrlPathTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CategoryUrlPathGenerator|MockObject
     */
    private $categoryUrlPathGenerator;

    /**
     * @var CategoryUrlRewriteGenerator|MockObject
     */
    private $categoryUrlRewriteGenerator;

    /**
     * @var StoreViewService|MockObject
     */
    private $storeViewService;

    /**
     * @var UrlPersistInterface|MockObject
     */
    private $urlPersist;

    /**
     * @var CategoryResource|MockObject
     */
    private $categoryResource;

    /**
     * @var Category|MockObject
     */
    private $category;

    /**
     * @var UpdateUrlPath
     */
    private $updateUrlPathPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->categoryUrlPathGenerator = $this->createPartialMock(
            CategoryUrlPathGenerator::class,
            ['getUrlPath']
        );
        $this->categoryUrlRewriteGenerator = $this->createPartialMock(
            CategoryUrlRewriteGenerator::class,
            ['generate']
        );
        $this->categoryResource = $this->createPartialMock(
            CategoryResource::class,
            ['saveAttribute']
        );
        $this->category = $this->createPartialMockWithReflection(
            Category::class,
            [
                'unsUrlPath',
                'setUrlPath',
                'getStoreId',
                'getParentId',
                'isObjectNew',
                'isInRootCategoryList',
                'getStoreIds',
                'setStoreId'
            ]
        );
        $this->storeViewService = $this->createPartialMock(
            StoreViewService::class,
            ['doesEntityHaveOverriddenUrlPathForStore']
        );
        $this->urlPersist = $this->createMock(UrlPersistInterface::class);

        $this->updateUrlPathPlugin = $this->objectManager->getObject(
            UpdateUrlPath::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'categoryUrlRewriteGenerator' => $this->categoryUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist,
                'storeViewService' => $this->storeViewService,
            ]
        );
    }

    public function testAroundSaveWithoutRootCategory()
    {
        $this->category->expects($this->atLeastOnce())->method('getParentId')->willReturn(0);
        $this->category->expects($this->atLeastOnce())->method('isObjectNew')->willReturn(true);
        $this->category->expects($this->atLeastOnce())->method('isInRootCategoryList')->willReturn(false);
        $this->category->expects($this->never())->method('getStoreIds');

        $this->assertEquals(
            $this->categoryResource,
            $this->updateUrlPathPlugin->afterSave($this->categoryResource, $this->categoryResource, $this->category)
        );
    }

    public function testAroundSaveWithRootCategory()
    {
        $parentId = 1;
        $categoryStoreIds = [0,1,2];
        $generatedUrlPath = 'parent_category/child_category';

        $this->categoryUrlPathGenerator
            ->expects($this->once())
            ->method('getUrlPath')
            ->with($this->category)
            ->willReturn($generatedUrlPath);
        $this->category->expects($this->atLeastOnce())->method('getParentId')->willReturn($parentId);
        $this->category->expects($this->atLeastOnce())->method('isObjectNew')->willReturn(true);
        $this->category->expects($this->atLeastOnce())->method('isInRootCategoryList')->willReturn(false);
        $this->category->expects($this->atLeastOnce())->method('getStoreIds')->willReturn($categoryStoreIds);
        $this->category->expects($this->once())->method('setStoreId')->with($categoryStoreIds[2])->willReturnSelf();
        $this->category->expects($this->once())->method('unsUrlPath')->willReturnSelf();
        $this->category->expects($this->once())->method('setUrlPath')->with($generatedUrlPath)->willReturnSelf();
        $this->storeViewService->expects($this->exactly(2))->method('doesEntityHaveOverriddenUrlPathForStore')
            ->willReturnMap(
                [
                    [$categoryStoreIds[1], $parentId, 'catalog_category', false],
                    [$categoryStoreIds[2], $parentId, 'catalog_category', true],
                ]
            );
        $this->categoryResource
            ->expects($this->once())
            ->method('saveAttribute')
            ->with($this->category, 'url_path')
            ->willReturnSelf();
        $generatedUrlRewrite = $this->createMock(UrlRewrite::class);
        $this->categoryUrlRewriteGenerator->expects($this->once())->method('generate')->with($this->category)
            ->willReturn([$generatedUrlRewrite]);
        $this->urlPersist->expects($this->once())->method('replace')->with([$generatedUrlRewrite])->willReturnSelf();

        $this->assertEquals(
            $this->categoryResource,
            $this->updateUrlPathPlugin->afterSave($this->categoryResource, $this->categoryResource, $this->category)
        );
    }
}
