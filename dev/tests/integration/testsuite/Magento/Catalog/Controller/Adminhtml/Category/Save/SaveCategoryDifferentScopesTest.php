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
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @magentoAppArea adminhtml
 */
class SaveCategoryDifferentScopesTest extends AbstractSaveCategoryTest
{
    private const GLOBAL_SCOPE_ID = 0;
    private const DEFAULT_STORE_ID = 1;

    private const FIXTURE_CATEGORY_ROOT = 400;
    private const FIXTURE_CATEGORY_CHILD = 401;
    private const FIXTURE_CATEGORY_LEAF = 402;

    private const FIXTURE_SECOND_STORE_CODE = 'fixturestore';

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var AdapterInterface */
    private $dbConnection;

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
     * For Test purposes you may need FRESH Category repository without internal cache
     *
     * @return CategoryRepositoryInterface
     */
    private function createCategoryRepository(): CategoryRepositoryInterface
    {
        return $this->_objectManager->create(CategoryRepositoryInterface::class);
    }

    /**
     * Change of `url_key` for specific store should not affect global value of `url_path`
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testUrlKeyUpdateInCustomStoreDoesNotChangeGlobalUrlPath()
    {
        $secondStoreId = $this->getStoreId(self::FIXTURE_SECOND_STORE_CODE);

        $storeScopeUrlKey = 'url-key-store-1';

        $updateCategoryStoreScope = [
            'entity_id' => self::FIXTURE_CATEGORY_LEAF,
            'store_id' => $secondStoreId,
            'url_key' => $storeScopeUrlKey,
            'use_config' => [
                'available_sort_by' => 1,
            ]
        ];
        $responseData = $this->performSaveCategoryRequest($updateCategoryStoreScope);
        $this->assertRequestIsSuccessfullyPerformed($responseData);

        $this->assertCategoryUrlPathForStore(
            self::FIXTURE_CATEGORY_LEAF,
            $secondStoreId,
            $this->getExpectedUrlPath([2 => $storeScopeUrlKey])
        );

        $this->assertCategoryUrlPathForStore(
            self::FIXTURE_CATEGORY_LEAF,
            self::GLOBAL_SCOPE_ID,
            $this->getExpectedUrlPath()
        );
    }

    /**
     * Magento EAV should not create entries for Default store (1) when Custom Store values are saved
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testUrlKeyUpdateInCustomStoreDoesNotCreateEntriesForDefaultStore()
    {
        $secondStoreId = $this->getStoreId(self::FIXTURE_SECOND_STORE_CODE);
        $storeScopeUrlKey = 'url-key-store-1';

        $updateCategoryStoreScope = [
            'entity_id' => self::FIXTURE_CATEGORY_LEAF,
            'store_id' => $secondStoreId,
            'url_key' => $storeScopeUrlKey,
            'use_config' => [
                'available_sort_by' => 1,
            ]
        ];
        $responseData = $this->performSaveCategoryRequest($updateCategoryStoreScope);
        $this->assertRequestIsSuccessfullyPerformed($responseData);

        $attributeValues = $this->getCategoryVarcharAttributeValuesPerScope(self::FIXTURE_CATEGORY_LEAF, 'url_path');
        $this->assertArrayNotHasKey(self::DEFAULT_STORE_ID, $attributeValues);
    }

    /**
     * Magento EAV should not create entries for Default store (1) when Global scope (0) values are saved
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testUrlKeyUpdateInGlobalScopeDoesNotCreateEntriesForDefaultStore()
    {
        $storeScopeUrlKey = 'url-key-store-1';

        $updateCategoryStoreScope = [
            'entity_id' => self::FIXTURE_CATEGORY_LEAF,
            'url_key' => $storeScopeUrlKey,
            'use_config' => [
                'available_sort_by' => 1,
            ]
        ];
        $responseData = $this->performSaveCategoryRequest($updateCategoryStoreScope);
        $this->assertRequestIsSuccessfullyPerformed($responseData);

        $attributeValues = $this->getCategoryVarcharAttributeValuesPerScope(self::FIXTURE_CATEGORY_LEAF, 'url_path');
        $this->assertArrayNotHasKey(self::DEFAULT_STORE_ID, $attributeValues);
    }

    /**
     * Change of `url_key` for root category in specific store should change all the children `url_path`
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testUpdateRootUrlKeyAffectsAllChildrenUrlPath()
    {
        $secondStoreId = $this->getStoreId(self::FIXTURE_SECOND_STORE_CODE);

        $this->assertCategoryUrlPathForStore(self::FIXTURE_CATEGORY_LEAF, $secondStoreId, $this->getExpectedUrlPath());

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

        $this->categoryRepository = $this->createCategoryRepository();
        $this->assertCategoryUrlPathForStore(self::FIXTURE_CATEGORY_LEAF, $secondStoreId,
            $this->getExpectedUrlPath([0 => 'store-root']));

        // Verify the potential side effects to Global Scope
        $this->assertCategoryUrlPathForStore(self::FIXTURE_CATEGORY_LEAF, self::DEFAULT_STORE_ID,
            $this->getExpectedUrlPath());
    }

    /**
     * After setting `use_default[url_key]` to `1` for Store, we expect that `url_path` will use Global `url_key`s
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     */
    public function testStoreScopeCategoryUrlPathUsesGlobalScopeUrlKey()
    {
        $this->markTestIncomplete('Still in Magento');
        $secondStoreId = $this->getStoreId(self::FIXTURE_SECOND_STORE_CODE);

        $updateSecondStoreCategoryUrlKey = [
            'entity_id' => self::FIXTURE_CATEGORY_CHILD,
            'url_key' => 'second-store-child',
            'store_id' => $secondStoreId,
            'use_config' => [
                'available_sort_by' => 1,
            ]
        ];
        $responseData = $this->performSaveCategoryRequest($updateSecondStoreCategoryUrlKey);
        $this->assertRequestIsSuccessfullyPerformed($responseData);

        // Temporary solution to reset Request object.
        $this->getRequest()->setDispatched(false);

        $this->assertCategoryUrlPathForStore(self::FIXTURE_CATEGORY_LEAF, $secondStoreId,
            $this->getExpectedUrlPath([1 => 'second-store-child']));

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

        $this->assertCategoryUrlPathForStore(self::FIXTURE_CATEGORY_LEAF, $secondStoreId, $this->getExpectedUrlPath());
    }

    /**
     * Returns StoreID by Code
     *
     * @param string $code
     * @return int
     * @throws NoSuchEntityException
     */
    private function getStoreId(string $code): int
    {
        return (int)$this->storeManager->getStore($code)->getId();
    }

    /**
     * Returns
     * @param array $replacements
     * @return string
     */
    private function getExpectedUrlPath(array $replacements = []): string
    {
        $basicUrlPath = ['category-1', 'category-1-1', 'category-1-1-1'];

        return implode('/', array_replace($basicUrlPath, $replacements));
    }

    /**
     * Returns array of attribute values per scope
     *
     * @param int $categoryId
     * @param string $attributeCode
     */
    private function getCategoryVarcharAttributeValuesPerScope(int $categoryId, string $attributeCode)
    {
        $selectValues = $this->getConnection()->select()
            ->from(
                ['ccev' => $this->getConnection()->getTableName('catalog_category_entity_varchar')],
                ['store_id', 'value']
            )
            ->join(
                ['ea' => $this->getConnection()->getTableName('eav_attribute')],
                'ccev.attribute_id = ea.attribute_id',
                []
            )
            ->where('entity_id = ?', $categoryId)
            ->where('ea.attribute_code = ?', $attributeCode);

        return $this->getConnection()->fetchPairs($selectValues);
    }

    /**
     * Asserts the URL path for the Category in specified Store scope
     *
     * @param int $categoryId
     * @param int $storeId
     * @param string $expectedPath
     * @throws NoSuchEntityException
     */
    private function assertCategoryUrlPathForStore(int $categoryId, int $storeId, string $expectedPath)
    {
        /** @var CategoryInterface|Category $storeScopeCategory */
        $storeScopeCategory = $this->categoryRepository->get($categoryId, $storeId);
        $this->assertSame($storeId, $storeScopeCategory->getStoreId());
        $this->assertSame($expectedPath, $storeScopeCategory->getData('url_path'));
    }

    private function getConnection(): AdapterInterface
    {
        if (null === $this->dbConnection) {
            /** @var ResourceConnection $resourceConnection */
            $resourceConnection = $this->_objectManager->create(ResourceConnection::class);
            $this->dbConnection = $resourceConnection->getConnection();
        }

        return $this->dbConnection;
    }
}
