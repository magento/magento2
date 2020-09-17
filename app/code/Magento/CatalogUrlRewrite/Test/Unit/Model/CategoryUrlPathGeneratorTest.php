<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryUrlPathGeneratorTest extends TestCase
{
    /** @var CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfig;

    /** @var CategoryRepositoryInterface|MockObject */
    protected $categoryRepository;

    /** @var Category|MockObject */
    protected $category;

    protected function setUp(): void
    {
        $this->category = $this->getMockBuilder(Category::class)
            ->addMethods(['getUrlPath'])
            ->onlyMethods(
                [
                    '__wakeup',
                    'getParentId',
                    'getLevel',
                    'dataHasChangedFor',
                    'getUrlKey',
                    'getStoreId',
                    'getId',
                    'formatUrlKey',
                    'getName',
                    'isObjectNew'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->categoryRepository = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);

        $this->categoryUrlPathGenerator = (new ObjectManager($this))->getObject(
            CategoryUrlPathGenerator::class,
            [
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
                'categoryRepository' => $this->categoryRepository,
            ]
        );
    }

    /**
     * @dataProvider getUrlPathDataProvider
     * @param int $parentId
     * @param string $urlPath
     * @param int $level
     * @param string $urlKey
     * @param bool $dataChangedForUrlKey
     * @param bool $dataChangedForParentId
     * @param string $result
     */
    public function testGetUrlPath(
        $parentId,
        $urlPath,
        $level,
        $urlKey,
        $dataChangedForUrlKey,
        $dataChangedForParentId,
        $result
    ) {
        $this->category->expects($this->any())->method('getParentId')->willReturn($parentId);
        $this->category->expects($this->any())->method('isObjectNew')->willReturn(false);
        $this->category->expects($this->any())->method('getLevel')->willReturn($level);
        $this->category->expects($this->any())->method('getUrlPath')->willReturn($urlPath);
        $this->category->expects($this->any())->method('getUrlKey')->willReturn($urlKey);
        $this->category->expects($this->any())->method('dataHasChangedFor')
            ->willReturnMap([['url_key', $dataChangedForUrlKey], ['parent_id', $dataChangedForParentId]]);

        $this->assertEquals($result, $this->categoryUrlPathGenerator->getUrlPath($this->category));
    }

    /**
     * @return array
     */
    public function getUrlPathDataProvider()
    {
        $noGenerationLevel = CategoryUrlPathGenerator::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING - 1;
        return [
            [Category::TREE_ROOT_ID, 'url-path', $noGenerationLevel, '', false, false, ''],
            [13, 'url-path', $noGenerationLevel, '', false, false, 'url-path'],
            [13, 'url-path', $noGenerationLevel, 'url-key', true, false, 'url-key'],
            [13, 'url-path', $noGenerationLevel, 'url-key', false, true, 'url-key'],
        ];
    }

    /**
     * @return array
     */
    public function getUrlPathWithParentDataProvider()
    {
        $requireGenerationLevel = CategoryUrlPathGenerator::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING;
        $noGenerationLevel = CategoryUrlPathGenerator::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING - 1;
        return [
            ['url-key', false, $requireGenerationLevel, 13, 'parent-path', 'parent-path/url-key'],
            ['url-key', false, $requireGenerationLevel, Category::TREE_ROOT_ID, null, 'url-key'],
            ['url-key', true, $noGenerationLevel, Category::TREE_ROOT_ID, null, 'url-key'],
        ];
    }

    /**
     * @dataProvider getUrlPathWithParentDataProvider
     * @param string $urlKey
     * @param bool $isCategoryNew
     * @param bool $level
     * @param int $parentCategoryParentId
     * @param string $parentUrlPath
     * @param string $result
     */
    public function testGetUrlPathWithParent(
        $urlKey,
        $isCategoryNew,
        $level,
        $parentCategoryParentId,
        $parentUrlPath,
        $result
    ) {
        $urlPath = null;
        $parentLevel = CategoryUrlPathGenerator::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING - 1;
        $this->category->expects($this->any())->method('getParentId')
            ->willReturn(13);
        $this->category->expects($this->any())->method('getLevel')
            ->willReturn($level);
        $this->category->expects($this->any())->method('getUrlPath')->willReturn($urlPath);
        $this->category->expects($this->any())->method('getUrlKey')->willReturn($urlKey);
        $this->category->expects($this->any())->method('isObjectNew')->willReturn($isCategoryNew);

        $parentCategory = $this->getMockBuilder(Category::class)
            ->addMethods(['getUrlPath'])
            ->onlyMethods(['__wakeup', 'getParentId', 'getLevel', 'dataHasChangedFor', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $parentCategory->expects($this->any())->method('getParentId')
            ->willReturn($parentCategoryParentId);
        $parentCategory->expects($this->any())->method('getLevel')->willReturn($parentLevel);
        $parentCategory->expects($this->any())->method('getUrlPath')->willReturn($parentUrlPath);
        $parentCategory->expects($this->any())->method('dataHasChangedFor')
            ->willReturnMap([['url_key', false], ['path_ids', false]]);

        $this->categoryRepository->expects($this->any())->method('get')->with(13)
            ->willReturn($parentCategory);

        $this->assertEquals($result, $this->categoryUrlPathGenerator->getUrlPath($this->category));
    }

    /**
     * @return array
     */
    public function getUrlPathWithSuffixDataProvider()
    {
        return [
            ['url-path', 1, null, '.html', 'url-path.html'],
            ['url-path', null, 1, '.html', 'url-path.html'],
        ];
    }

    /**
     * @dataProvider getUrlPathWithSuffixDataProvider
     * @param string $urlPath
     * @param int $storeId
     * @param int $categoryStoreId
     * @param string $suffix
     * @param string $result
     */
    public function testGetUrlPathWithSuffixAndStore($urlPath, $storeId, $categoryStoreId, $suffix, $result)
    {
        $this->category->expects($this->any())->method('getStoreId')->willReturn($categoryStoreId);
        $this->category->expects($this->once())->method('getParentId')->willReturn(123);
        $this->category->expects($this->once())->method('getUrlPath')->willReturn($urlPath);
        $this->category->expects($this->exactly(2))->method('dataHasChangedFor')
            ->willReturnMap([['url_key', false], ['path_ids', false]]);

        $passedStoreId = $storeId ? $storeId : $categoryStoreId;
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX, ScopeInterface::SCOPE_STORE, $passedStoreId)
            ->willReturn($suffix);

        $this->assertEquals(
            $result,
            $this->categoryUrlPathGenerator->getUrlPathWithSuffix($this->category, $storeId)
        );
    }

    public function testGetUrlPathWithSuffixWithoutStore()
    {
        $urlPath = 'url-path';
        $storeId = null;
        $currentStoreId = 1;
        $suffix = '.html';
        $result = 'url-path.html';

        $this->category->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $this->category->expects($this->once())->method('getParentId')->willReturn(2);
        $this->category->expects($this->once())->method('getUrlPath')->willReturn($urlPath);
        $this->category->expects($this->exactly(2))->method('dataHasChangedFor')
            ->willReturnMap([['url_key', false], ['path_ids', false]]);

        $store = $this->createMock(Store::class);
        $store->expects($this->once())->method('getId')->willReturn($currentStoreId);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with(CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX, ScopeInterface::SCOPE_STORE, $currentStoreId)
            ->willReturn($suffix);

        $this->assertEquals(
            $result,
            $this->categoryUrlPathGenerator->getUrlPathWithSuffix($this->category, $storeId)
        );
    }

    public function testGetCanonicalUrlPath()
    {
        $this->category->expects($this->once())->method('getId')->willReturn(1);
        $this->assertEquals(
            'catalog/category/view/id/1',
            $this->categoryUrlPathGenerator->getCanonicalUrlPath($this->category)
        );
    }

    /**
     * @return array
     */
    public function getUrlKeyDataProvider()
    {
        return [
            ['url-key', null, 'url-key'],
            ['', 'category-name', 'category-name'],
        ];
    }

    /**
     * @dataProvider getUrlKeyDataProvider
     * @param string|null|bool $urlKey
     * @param string|null|bool $name
     * @param string $result
     */
    public function testGetUrlKey($urlKey, $name, $result)
    {
        $this->category->expects($this->once())->method('getUrlKey')->willReturn($urlKey);
        $this->category->expects($this->any())->method('getName')->willReturn($name);
        $this->category->expects($this->once())->method('formatUrlKey')->willReturnArgument(0);

        $this->assertEquals($result, $this->categoryUrlPathGenerator->getUrlKey($this->category));
    }
}
