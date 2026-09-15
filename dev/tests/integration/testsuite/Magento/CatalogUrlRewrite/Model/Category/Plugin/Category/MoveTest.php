<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that moving a category recalculates store-view-scoped url_path values
 * for the moved category and its descendants instead of losing them.
 */
class MoveTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/category_move_with_store_overrides.php
     */
    public function testMoveRecalculatesStoreScopedUrlPathForDescendants(): void
    {
        $store = $this->objectManager->create(Store::class);
        $store->load('fixture_second_store', 'code');
        $secondStoreId = (int)$store->getId();

        $category1 = $this->categoryRepository->get(3);
        $category1->move(6, null);

        $this->assertSame(
            'category-4/category-1-tt',
            $this->categoryRepository->get(3, $secondStoreId)->getUrlPath()
        );
        $this->assertSame(
            'category-4/category-1-tt/category-2-tt',
            $this->categoryRepository->get(4, $secondStoreId)->getUrlPath()
        );
        $this->assertSame(
            'category-4/category-1-tt/category-2-tt/category-3-tt',
            $this->categoryRepository->get(5, $secondStoreId)->getUrlPath()
        );
    }
}
