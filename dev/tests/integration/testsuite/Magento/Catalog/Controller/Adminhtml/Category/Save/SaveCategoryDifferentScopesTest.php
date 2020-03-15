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
 */
class SaveCategoryDifferentScopesTest extends AbstractSaveCategoryTest
{
    private const DEFAULT_STORE_ID = 1;
    private const FIXTURE_CATEGORY_ID = 333;
    private const GLOBAL_SCOPE_ID = 0;

    private const FIXTURE_URL_KEY = 'category-1';

    private const FIXTURE_CATEGORY_ROOT = 400;
    private const FIXTURE_CATEGORY_CHILD = 401;
    private const FIXTURE_CATEGORY_LEAF = 402;

    private const FIXTURE_SECOND_STORE_CODE = 'fixturestore';

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->categoryRepository = null;
        $this->storeManager = null;
        parent::tearDown();
    }

    /**
     * Change of `url_key` for specific store should not affect global value of `url_path`
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testChangeUrlKeyForSpecificStoreShouldNotChangeGlobalUrlPath()
    {
        $expectedStoreScopeUrlKey = 'url-key-store-1';
        $expectedGlobalScopeUrlKey = self::FIXTURE_URL_KEY;

        $postData = [
            'entity_id' => self::FIXTURE_CATEGORY_ID,
            'store_id' => self::DEFAULT_STORE_ID,
            'url_key' => $expectedStoreScopeUrlKey
        ];
        $responseData = $this->performSaveCategoryRequest($postData);
        $this->assertRequestIsSuccessfullyPerformed($responseData);

        /** @var CategoryInterface|Category $storeScopeCategory */
        $storeScopeCategory = $this->categoryRepository->get(self::FIXTURE_CATEGORY_ID, self::DEFAULT_STORE_ID);
        $this->assertSame($expectedStoreScopeUrlKey, $storeScopeCategory->getData('url_path'));

        $globalScopeCategory = $this->categoryRepository->get(self::FIXTURE_CATEGORY_ID, self::GLOBAL_SCOPE_ID);
        $this->assertSame($expectedGlobalScopeUrlKey, $globalScopeCategory->getData('url_path'));
    }

    /**
     * Change of `url_key` for root category in specific store should change all the children `url_path`
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testChangeUrlKeyAffectsAllChildrenUrlPath()
    {
        $secondStoreId = $this->getStoreId(self::FIXTURE_SECOND_STORE_CODE);

        $urlKeys = [
            1 => 'category-1',
            2 => 'category-1-1',
            3 => 'category-1-1-1'
        ];

        /** @var CategoryInterface|Category $leafOriginal */
        $leafOriginal = $this->categoryRepository->get(self::FIXTURE_CATEGORY_LEAF);
        $this->assertSame(implode('/', $urlKeys), $leafOriginal->getData('url_path'));

        $updateRootData = [
            'entity_id' => self::FIXTURE_CATEGORY_ROOT,
            'store_id' => $secondStoreId,
            'url_key' => 'store-root',
            'use_config' => [
                'available_sort_by' => 1,
            ]
        ];
        $responseData = $this->performSaveCategoryRequest($updateRootData);
        $this->assertRequestIsSuccessfullyPerformed($responseData);

        $newUrlKeys = $urlKeys;
        $newUrlKeys[1] = 'store-root';

        /** @var CategoryInterface|Category $leafStoreScope */
        $leafStoreScope = $this->categoryRepository->get(self::FIXTURE_CATEGORY_LEAF, $secondStoreId);
        $this->assertSame(implode('/', $newUrlKeys), $leafStoreScope->getData('url_path'));

        /** @var CategoryInterface|Category $leafGlobalScope */
        $leafGlobalScope = $this->categoryRepository->get(self::FIXTURE_CATEGORY_LEAF);
        $this->assertSame(implode('/', $urlKeys), $leafGlobalScope->getData('url_path'));
    }

    /**
     * After setting `use_default[url_key]` to `1` for Store, we expect that `url_path` will use Global `url_key`s
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     */
    public function testCategoryUrlPathUsesGlobalUrlKey()
    {
        $secondStoreId = $this->getStoreId(self::FIXTURE_SECOND_STORE_CODE);

        $setSecondStoreCategoryUrlKey = [
            'entity_id' => self::FIXTURE_CATEGORY_CHILD,
            'url_key' => 'second-store-child',
            'store_id' => $secondStoreId,
            'use_config' => [
                'available_sort_by' => 1,
            ]
        ];
        $responseData = $this->performSaveCategoryRequest($setSecondStoreCategoryUrlKey);
        $this->assertRequestIsSuccessfullyPerformed($responseData);

        /** @var CategoryInterface|Category $categoryWithCustomUrlKey */
        $categoryWithCustomUrlKey = $this->categoryRepository->get(self::FIXTURE_CATEGORY_CHILD, $secondStoreId);
        $this->assertSame('category-1/second-store-child', $categoryWithCustomUrlKey->getData('url_path'));

        $useDefaultUrlKey = [
            'entity_id' => self::FIXTURE_CATEGORY_CHILD,
            'use_default' => [
                'url_key' => 1
            ],
            'store_id' => $secondStoreId,
            'use_config' => [
                'available_sort_by' => 1,
            ]
        ];
        $responseData = $this->performSaveCategoryRequest($useDefaultUrlKey);
        $this->assertRequestIsSuccessfullyPerformed($responseData);

        $categoryWithDefaultUrlKey = $this->categoryRepository->get(self::FIXTURE_CATEGORY_LEAF, $secondStoreId);
        $this->assertSame('category-1/category-1-1/category-1-1-1', $categoryWithDefaultUrlKey->getData('url_path'));
    }

    /**
     * Returns StoreID by Code
     *
     * @param string $code
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreId(string $code): int
    {
        return (int)$this->storeManager->getStore($code)->getId();
    }
}
