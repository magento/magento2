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
     * url_key overrides.
     */
    public function testAfterChangeParentRecalculatesUrlPathForEveryStore()
    {
        $urlPath = 'test/path';
        $storeIds = [0, 1, 2];

        // Simulate the category's real store-scope state so the test fails if any
        // code path leaks/forgets to restore the store id it mutated.
        $storeIdState = 0;
        $this->categoryMock->expects($this->exactly(10))->method('getStoreId')->willReturnCallback(
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

        $this->categoryMock->expects($this->exactly(3))->method('getId')->willReturnSelf();

        $this->storeManagerMock->expects($this->exactly(3))->method('hasSingleStore')->willReturn(false);
        $this->categoryMock->expects($this->once())->method('getStoreIds')->willReturn($storeIds);
        $this->categoryMock->expects($this->exactly(3))->method('getOrigData')
            ->with('path')->willReturn('1/2/5');
        $this->categoryMock->expects($this->exactly(3))->method('getData')
            ->with('path')->willReturn('1/3/6/5');

        $this->categoryMock->expects($this->exactly(6))->method('unsUrlPath')->willReturnSelf();
        $this->categoryMock->expects($this->exactly(5))->method('setUrlPath')->with($urlPath);
        $this->categoryMock->expects($this->exactly(3))->method('setUrlKey')->with('url-key');
        $this->categoryMock->expects($this->exactly(6))->method('getResource')->willReturn($this->subjectMock);

        // A child category only exists in the tree fetched for store 1, so its
        // url_path handling must only occur while the loop is processing store 1.
        $childMock = $this->createPartialMockWithReflection(
            Category::class,
            ['getResource', 'setStoreId', 'unsUrlPath', 'setUrlPath']
        );
        $childMock->expects($this->once())->method('setStoreId')->with(1);
        $childMock->expects($this->exactly(2))->method('unsUrlPath')->willReturnSelf();
        $childMock->expects($this->once())->method('setUrlPath')->with($urlPath);
        $childResourceMock = $this->createPartialMock(CategoryResourceModel::class, ['saveAttribute']);
        $childResourceMock->expects($this->exactly(2))->method('saveAttribute')->with($childMock, 'url_path');
        $childMock->expects($this->exactly(2))->method('getResource')->willReturn($childResourceMock);

        $requestedChildStoreIds = [];
        $this->childrenCategoriesProviderMock->expects($this->exactly(6))
            ->method('getChildren')
            ->with($this->categoryMock, true, $this->callback(
                function ($storeId) use (&$requestedChildStoreIds) {
                    $requestedChildStoreIds[] = $storeId;
                    return true;
                }
            ))
            ->willReturnCallback(
                function ($category, $recursive, $storeId) use ($childMock) {
                    return $storeId === 1 ? [$childMock] : [];
                }
            );

        $savedAtStoreScope = [];
        $this->subjectMock->expects($this->exactly(6))->method('saveAttribute')
            ->with($this->categoryMock, 'url_path')
            ->willReturnCallback(
                function () use (&$savedAtStoreScope, &$storeIdState) {
                    $savedAtStoreScope[] = $storeIdState;
                    return $this->subjectMock;
                }
            );

        $originalCategory = $this->createMock(Category::class);
        $originalCategory->method('getUrlKey')->willReturn('url-key');
        $originalCategory->method('load')->willReturnSelf();
        $urlKeyReloadStoreIds = [];
        $originalCategory->expects($this->exactly(3))->method('setStoreId')
            ->willReturnCallback(
                function ($storeId) use ($originalCategory, &$urlKeyReloadStoreIds) {
                    $urlKeyReloadStoreIds[] = $storeId;
                    return $originalCategory;
                }
            );
        $this->categoryFactory->expects($this->exactly(3))->method('create')
            ->willReturn($originalCategory);

        $this->categoryUrlPathGeneratorMock->expects($this->exactly(6))->method('getUrlPath')
            ->willReturn($urlPath);

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
        // store's own scope (0, then 1, then 2) - not repeatedly at store 0.
        $this->assertSame([0, 0, 0, 1, 0, 2], $savedAtStoreScope);
        // The store-specific url_key must be reloaded for the actual store being
        // processed, not the original (default) scope the move started from.
        $this->assertSame([0, 1, 2], $urlKeyReloadStoreIds);
        // Descendants must be fetched scoped to the store currently being processed.
        $this->assertSame([0, 0, 1, 1, 2, 2], $requestedChildStoreIds);
    }
}
