<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category\Save;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class SaveCategoryDifferentScopesTest extends AbstractSaveCategoryTest
{
    private const DEFAULT_STORE_ID = 1;
    private const FIXTURE_CATEGORY_ID = 333;
    private const GLOBAL_SCOPE_ID = 0;

    private const FIXTURE_URL_KEY = 'category-1';

    private const FIXTURE_CATEGORY_ROOT = 400;
    private const FIXTURE_CATEGORY_LEAF = 402;

    private const FIXTURE_SECOND_STORE_CODE = 'fixturestore';

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testUpdateUrlKeyForStoreExpectNoChangeOfGlobalUrlPath()
    {
        $storeScopeUrlKey = 'url-key-store-1';

        $postData = [
            'entity_id' => self::FIXTURE_CATEGORY_ID,
            'store_id' => self::DEFAULT_STORE_ID,
            'url_key' => $storeScopeUrlKey
        ];

        $this->performSaveCategoryRequest($postData);

        /** @var CategoryInterface|Category $storeScopeCategory */
        $storeScopeCategory = $this->categoryRepository->get(self::FIXTURE_CATEGORY_ID, self::DEFAULT_STORE_ID);
        $this->assertSame($storeScopeUrlKey, $storeScopeCategory->getData('url_path'));

        $globalScopeCategory = $this->categoryRepository->get(self::FIXTURE_CATEGORY_ID, self::GLOBAL_SCOPE_ID);
        $this->assertSame(self::FIXTURE_URL_KEY, $globalScopeCategory->getData('url_path'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testUpdateParentCategoryUrlKeyForStoreScopeAffectsOnlyChildCategoryForStoreScope()
    {
        /** @var CategoryInterface|Category $leafOriginal */
        $leafOriginal = $this->categoryRepository->get(self::FIXTURE_CATEGORY_LEAF);
        $this->assertSame('category-1/category-1-1/category-1-1-1', $leafOriginal->getData('url_path'));

        $secondStoreId = $this->getStoreId(self::FIXTURE_SECOND_STORE_CODE);

        $updateRootData = [
            'entity_id' => self::FIXTURE_CATEGORY_ROOT,
            'store_id' => $secondStoreId,
            'url_key' => 'store-root',
            'use_config' => [
                'available_sort_by' => 1,
            ]
        ];

        $this->performSaveCategoryRequest($updateRootData);

        /** @var CategoryInterface|Category $leafStoreScope */
        $leafStoreScope = $this->categoryRepository->get(self::FIXTURE_CATEGORY_LEAF, $secondStoreId);
        $this->assertSame('store-root/category-1-1/category-1-1-1', $leafStoreScope->getData('url_path'));

        /** @var CategoryInterface|Category $leafGlobalScope */
        $leafGlobalScope = $this->categoryRepository->get(self::FIXTURE_CATEGORY_LEAF);
        $this->assertSame('category-1/category-1-1/category-1-1-1', $leafGlobalScope->getData('url_path'));
    }

    private function getStoreId(string $code): int
    {
        return (int)$this->storeManager->getStore($code)->getId();
    }
}
