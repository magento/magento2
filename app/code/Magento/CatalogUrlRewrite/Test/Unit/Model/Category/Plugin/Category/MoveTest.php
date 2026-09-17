<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\Move as CategoryMovePlugin;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MoveTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ChildrenCategoriesProvider|MockObject
     */
    private $childrenCategoriesProviderMock;

    /**
     * @var CategoryUrlPathGenerator|MockObject
     */
    private $categoryUrlPathGeneratorMock;

    /**
     * @var CategoryResourceModel|MockObject
     */
    private $subjectMock;

    /**
     * @var Category|MockObject
     */
    private $categoryMock;

    /**
     * @var CategoryFactory|MockObject
     */
    private $categoryFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var CategoryMovePlugin
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->categoryUrlPathGeneratorMock = $this->createPartialMock(
            CategoryUrlPathGenerator::class,
            ['getUrlPath']
        );
        $this->childrenCategoriesProviderMock = $this->createPartialMock(
            ChildrenCategoriesProvider::class,
            ['getChildren']
        );
        $this->categoryFactory = $this->createMock(CategoryFactory::class);
        $this->subjectMock = $this->createPartialMock(
            CategoryResourceModel::class,
            ['saveAttribute']
        );
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->categoryMock = $this->createPartialMockWithReflection(
            Category::class,
            [
                'getResource',
                'getStoreIds',
                'getStoreId',
                'setStoreId',
                'getData',
                'getOrigData',
                'getId',
                'getUrlKey',
                'setUrlPath',
                'unsUrlPath',
                'setUrlKey'
            ]
        );
        $this->plugin = $this->objectManager->getObject(
            CategoryMovePlugin::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGeneratorMock,
                'childrenCategoriesProvider' => $this->childrenCategoriesProviderMock,
                'categoryFactory' => $this->categoryFactory,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Moving a category must recalculate url_path for every store it belongs to
     * (not just the default scope), including descendants that have store-specific
     * url_key overrides. Every store's own url_key must be freshly reloaded before
     * each recompute - including the "refresh the default scope" pass that runs
     * while a non-default store is being processed - so a second (or later)
     * non-default store never gets its url_path computed from a url_key left over
     * from whichever store was processed previously, and the default scope's own
     * refresh never saves a null/unset url_path (which - since Category's resource
     * model doesn't scope a null save by store - would delete every store's value,
     * not just the default scope's).
     */
    public function testAfterChangeParentRecalculatesUrlPathForEveryStore(): void
    {
        $urlPath = 'test/path';
        $storeIdState = 0;
        $categoryUrlKeyState = null;
        $savedAtStoreScope = [];
        $urlKeyReloadStoreIds = [];
        $requestedChildStoreIds = [];

        $this->mockCategoryStoreScopeTracking($storeIdState);
        $this->mockCategoryUrlKeyTracking($categoryUrlKeyState);
        $this->storeManagerMock->expects($this->exactly(3))->method('hasSingleStore')->willReturn(false);
        $this->categoryMock->expects($this->once())->method('getStoreIds')->willReturn([0, 1, 2]);
        $this->categoryMock->expects($this->exactly(3))->method('getOrigData')
            ->with('path')->willReturn('1/2/5');
        $this->categoryMock->expects($this->exactly(3))->method('getData')
            ->with('path')->willReturn('1/3/6/5');
        $this->categoryMock->expects($this->exactly(6))->method('getId')->willReturnSelf();
        $this->categoryMock->expects($this->exactly(6))->method('unsUrlPath')->willReturnSelf();
        $this->categoryMock->expects($this->exactly(6))->method('setUrlPath');
        $this->categoryMock->expects($this->exactly(6))->method('getResource')->willReturn($this->subjectMock);

        // A child category only exists in the tree fetched for store 1, so its
        // url_path handling must only occur while the loop is processing store 1.
        $childMock = $this->mockChildCategory($urlPath);
        $this->mockChildrenProvider($childMock, $requestedChildStoreIds);
        $this->mockSaveAttributeRecording($storeIdState, $categoryUrlKeyState, $savedAtStoreScope);
        $this->mockUrlKeyReload($urlKeyReloadStoreIds);
        $this->categoryUrlPathGeneratorMock->expects($this->exactly(7))->method('getUrlPath')
            ->willReturnCallback(
                function ($category) use (&$categoryUrlKeyState, $urlPath, $childMock) {
                    return $category === $childMock ? $urlPath : 'path:' . $categoryUrlKeyState;
                }
            );

        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterChangeParent(
                $this->subjectMock,
                $this->subjectMock,
                $this->categoryMock,
                $this->categoryMock,
                null
            )
        );

        // The category's own url_path must be recalculated and saved at each
        // store's own scope, using THAT store's own url_key - never null (a null
        // save here would delete every store's value, not just the current one),
        // never repeatedly at store 0 alone, and never using a url_key left over
        // from a store processed earlier in the loop (store 0's re-save while
        // processing store 2 must still use store 0's own key, not store 1's,
        // which was processed just before it).
        $this->assertSame(
            [
                [0, 'key-for-store-0'],
                [0, 'key-for-store-0'],
                [0, 'key-for-store-0'],
                [1, 'key-for-store-1'],
                [0, 'key-for-store-0'],
                [2, 'key-for-store-2'],
            ],
            $savedAtStoreScope
        );
        // The store-specific url_key must be reloaded for the actual store being
        // processed, not the original (default) scope the move started from -
        // including the extra reload while refreshing the default scope's entry
        // during every other store's iteration (including the default store's own).
        $this->assertSame([0, 0, 0, 1, 0, 2], $urlKeyReloadStoreIds);
        // Descendants must be fetched scoped to the store currently being processed.
        $this->assertSame([0, 1, 2], $requestedChildStoreIds);
    }

    /**
     * Simulate the category's real store-scope state so the test fails if any
     * code path leaks/forgets to restore the store id it mutated.
     *
     * @param int $storeIdState
     * @return void
     */
    private function mockCategoryStoreScopeTracking(int &$storeIdState): void
    {
        $this->categoryMock->expects($this->exactly(13))->method('getStoreId')->willReturnCallback(
            function () use (&$storeIdState) {
                return $storeIdState;
            }
        );
        $this->categoryMock->expects($this->exactly(8))->method('setStoreId')->willReturnCallback(
            function ($id) use (&$storeIdState) {
                $storeIdState = $id;
                return $this->categoryMock;
            }
        );
    }

    /**
     * Track what url_key is actually set on the category, instead of asserting a
     * single fixed value, so a stale/wrong-store url_key would be caught.
     *
     * @param string|null $categoryUrlKeyState
     * @return void
     */
    private function mockCategoryUrlKeyTracking(?string &$categoryUrlKeyState): void
    {
        $this->categoryMock->expects($this->exactly(6))->method('setUrlKey')->willReturnCallback(
            function ($urlKey) use (&$categoryUrlKeyState) {
                $categoryUrlKeyState = $urlKey;
                return $this->categoryMock;
            }
        );
    }

    /**
     * @param string $urlPath
     * @return Category|MockObject
     */
    private function mockChildCategory(string $urlPath): MockObject
    {
        $childMock = $this->createPartialMockWithReflection(
            Category::class,
            ['getResource', 'setStoreId', 'unsUrlPath', 'setUrlPath']
        );
        $childMock->expects($this->once())->method('setStoreId')->with(1);
        $childMock->expects($this->once())->method('unsUrlPath')->willReturnSelf();
        $childMock->expects($this->once())->method('setUrlPath')->with($urlPath);
        $childResourceMock = $this->createPartialMock(CategoryResourceModel::class, ['saveAttribute']);
        $childResourceMock->expects($this->once())->method('saveAttribute')->with($childMock, 'url_path');
        $childMock->expects($this->once())->method('getResource')->willReturn($childResourceMock);

        return $childMock;
    }

    /**
     * @param MockObject $childMock
     * @param array $requestedChildStoreIds
     * @return void
     */
    private function mockChildrenProvider(MockObject $childMock, array &$requestedChildStoreIds): void
    {
        $this->childrenCategoriesProviderMock->expects($this->exactly(3))
            ->method('getChildren')
            ->with($this->categoryMock, true, $this->callback(
                function ($storeId) use (&$requestedChildStoreIds) {
                    $requestedChildStoreIds[] = $storeId;
                    return true;
                }
            ))
            ->willReturnCallback(
                function (...$args) use ($childMock) {
                    return $args[2] === 1 ? [$childMock] : [];
                }
            );
    }

    /**
     * @param int $storeIdState
     * @param string|null $categoryUrlKeyState
     * @param array $savedAtStoreScope
     * @return void
     */
    private function mockSaveAttributeRecording(
        int &$storeIdState,
        ?string &$categoryUrlKeyState,
        array &$savedAtStoreScope
    ): void {
        $this->subjectMock->expects($this->exactly(6))->method('saveAttribute')
            ->with($this->categoryMock, 'url_path')
            ->willReturnCallback(
                function () use (&$savedAtStoreScope, &$storeIdState, &$categoryUrlKeyState) {
                    $savedAtStoreScope[] = [$storeIdState, $categoryUrlKeyState];
                    return $this->subjectMock;
                }
            );
    }

    /**
     * Simulate each store genuinely having its own distinct url_key, so reusing
     * a value reloaded for a different store is observable.
     *
     * @param array $urlKeyReloadStoreIds
     * @return void
     */
    private function mockUrlKeyReload(array &$urlKeyReloadStoreIds): void
    {
        $originalCategory = $this->createMock(Category::class);
        $originalCategoryStoreId = null;
        $originalCategory->method('load')->willReturnSelf();
        $originalCategory->method('getUrlKey')->willReturnCallback(
            function () use (&$originalCategoryStoreId) {
                return 'key-for-store-' . $originalCategoryStoreId;
            }
        );
        $originalCategory->expects($this->exactly(6))->method('setStoreId')
            ->willReturnCallback(
                function ($storeId) use ($originalCategory, &$urlKeyReloadStoreIds, &$originalCategoryStoreId) {
                    $urlKeyReloadStoreIds[] = $storeId;
                    $originalCategoryStoreId = $storeId;
                    return $originalCategory;
                }
            );
        $this->categoryFactory->expects($this->exactly(6))->method('create')
            ->willReturn($originalCategory);
    }
}
