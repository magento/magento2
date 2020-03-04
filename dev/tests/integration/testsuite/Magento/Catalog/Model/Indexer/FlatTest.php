<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class FlatTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var int
     */
    private static $defaultCategoryId = 2;

    /**
     * @var int
     */
    protected static $categoryOne;

    /**
     * @var int
     */
    protected static $categoryTwo;

    /**
     * List of attribute codes
     *
     * @var string[]
     */
    protected static $attributeCodes = [];

    /**
     * List of attribute values
     * Data loaded from EAV
     *
     * @var string[]
     */
    protected static $attributeValues = [];

    /**
     * List of attributes to exclude
     *
     * @var string[]
     */
    protected static $attributesToExclude = ['url_path', 'display_mode'];

    /**
     * @var int
     * @deprecated
     */
    protected static $totalBefore = 0;

    public static function setUpBeforeClass()
    {
        self::loadAttributeCodes();

        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }

    public function testEntityItemsBefore()
    {
        $category = $this->instantiateCategoryModel();
        $result = $category->getCollection()->getAllIds();
        $this->assertNotEmpty($result);
        $this->assertTrue(is_array($result));
    }

    /**
     * Reindex All
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     * @magentoAppArea frontend
     *
     * @magentoDbIsolation disabled
     */
    public function testReindexAll()
    {
        /** @var  $indexer \Magento\Framework\Indexer\IndexerInterface */
        $indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Indexer\Model\Indexer::class
        );
        $indexer->load('catalog_category_flat');
        $indexer->reindexAll();
        $this->assertTrue($indexer->isValid());

        $category = $this->getLoadedDefaultCategory();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Category\Flat::class, $category->getResource());
        $this->checkCategoryData($category);
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     * @magentoAppArea frontend
     */
    public function testFlatItemsBefore()
    {
        $category = $this->getLoadedDefaultCategory();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Category\Flat::class, $category->getResource());

        $result = $category->getAllChildren(true);
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
    }

    /**
     * Populate EAV category data`
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     *
     * @magentoDbIsolation disabled
     */
    public function testCreateCategory()
    {
        $this->createSubCategoriesInDefaultCategory();

        $result = $this->getLoadedDefaultCategory()->getCollection()->getItems();
        $this->assertTrue(is_array($result));

        $this->assertEquals(self::$defaultCategoryId, $result[self::$categoryOne]->getParentId());
        $this->assertEquals(self::$categoryOne, $result[self::$categoryTwo]->getParentId());

        $this->removeSubCategoriesInDefaultCategory();
    }

    /**
     * Test for reindex row action
     * Check that category data created at testCreateCategory() were syncing to flat structure
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     * @magentoAppArea frontend
     *
     * @magentoDbIsolation disabled
     */
    public function testFlatAfterCreate()
    {
        $this->createSubCategoriesInDefaultCategory();

        $category = $this->getLoadedDefaultCategory();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Category\Flat::class, $category->getResource());

        $result = $category->getAllChildren(true);
        $this->assertNotEmpty($result);
        $this->assertCount(3, $result);
        $this->assertContains(self::$categoryOne, $result);

        $categoryOne = $this->getLoadedCategory(self::$categoryOne);
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Category\Flat::class, $categoryOne->getResource());

        $result = $categoryOne->getAllChildren(true);
        $this->assertNotEmpty($result);
        $this->assertCount(2, $result);
        $this->assertContains(self::$categoryTwo, $result);
        $this->checkCategoryData($categoryOne);

        $categoryTwo = $this->getLoadedCategory(self::$categoryTwo);
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Category\Flat::class, $categoryTwo->getResource());

        $this->assertEquals(self::$categoryOne, $categoryTwo->getParentId());
        $this->checkCategoryData($categoryTwo);

        $this->removeSubCategoriesInDefaultCategory();
    }

    /**
     * Move category and populate EAV category data
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     *
     * @magentoDbIsolation disabled
     */
    public function testMoveCategory()
    {
        $this->moveSubCategoriesInDefaultCategory();
        $categoryTwo = $this->getLoadedCategory(self::$categoryTwo);
        $this->assertEquals($categoryTwo->getData('parent_id'), self::$defaultCategoryId);

        $this->removeSubCategoriesInDefaultCategory();
    }

    /**
     * Test for reindex list action
     * Check that category data created at testMoveCategory() were syncing to flat structure
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     * @magentoAppArea frontend
     *
     * @magentoDbIsolation disabled
     */
    public function testFlatAfterMove()
    {
        $this->moveSubCategoriesInDefaultCategory();

        $category = $this->getLoadedDefaultCategory();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Category\Flat::class, $category->getResource());
        $this->checkCategoryData($category);

        $result = $category->getAllChildren(true);
        $this->assertNotEmpty($result);
        $this->assertCount(3, $result);

        $categoryOne = $this->getLoadedCategory(self::$categoryOne);
        $this->checkCategoryData($categoryOne);

        $categoryTwo = $this->getLoadedCategory(self::$categoryTwo);
        $this->checkCategoryData($categoryTwo);

        $this->removeSubCategoriesInDefaultCategory();
    }

    /**
     * Delete created categories at testCreateCategory()
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     * @magentoAppArea adminhtml
     */
    public function testDeleteCategory()
    {
        $countBeforeModification = count($this->instantiateCategoryModel()->getCollection()->getAllIds());

        $this->deleteSubCategoriesInDefaultCategory();

        $category = $this->instantiateCategoryModel();
        $result = $category->getCollection()->getAllIds();
        $this->assertNotEmpty($result);
        $this->assertTrue(is_array($result));
        $this->assertCount($countBeforeModification, $result);
    }

    /**
     * Test for reindex row action
     * Check that category data deleted at testDeleteCategory() were syncing to flat structure
     *
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_category true
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testFlatAfterDeleted()
    {
        $this->deleteSubCategoriesInDefaultCategory();

        $category = $this->getLoadedDefaultCategory();
        $this->assertInstanceOf(\Magento\Catalog\Model\ResourceModel\Category\Flat::class, $category->getResource());

        $result = $category->getAllChildren(true);
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
    }

    /**
     * Populate attribute values from category
     * Data loaded from EAV
     *
     * @param \Magento\Catalog\Model\Category $category
     */
    protected static function loadAttributeValues(\Magento\Catalog\Model\Category $category)
    {
        foreach (self::$attributeCodes as $attributeCode) {
            self::$attributeValues[$category->getId()][$attributeCode] = $category->getData($attributeCode);
        }
    }

    /**
     * Populate attribute codes for category entity
     * Data loaded from EAV
     *
     */
    protected static function loadAttributeCodes()
    {
        /** @var \Magento\Catalog\Model\Config $catalogConfig */
        $catalogConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Config::class
        );
        $attributeCodes = $catalogConfig->getEntityAttributeCodes(\Magento\Catalog\Model\Category::ENTITY);

        foreach ($attributeCodes as $attributeCode) {
            if (in_array($attributeCode, self::$attributesToExclude)) {
                continue;
            }
            self::$attributeCodes[] = $attributeCode;
        }
    }

    /**
     * Check EAV and flat data
     *
     * @param \Magento\Catalog\Model\Category $category
     */
    protected function checkCategoryData(\Magento\Catalog\Model\Category $category)
    {
        foreach (self::$attributeCodes as $attributeCode) {
            $this->assertEquals(
                self::$attributeValues[$category->getId()][$attributeCode],
                $category->getData($attributeCode),
                "Data for {$category->getId()} attribute code [{$attributeCode}] is wrong"
            );
        }
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    private function getLoadedDefaultCategory()
    {
        $category = $this->getLoadedCategory(self::$defaultCategoryId);

        return $category;
    }

    /**
     * @param int $categoryId
     * @return \Magento\Catalog\Model\Category
     */
    private function getLoadedCategory($categoryId)
    {
        $category = $this->instantiateCategoryModel();
        $category->load($categoryId);
        self::loadAttributeValues($category);
        return $category;
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    private function instantiateCategoryModel()
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
    }

    /**
     * Invoke business logic:
     * - create child category in the Default category
     * - create child category in category created in previous step
     */
    private function createSubCategoriesInDefaultCategory()
    {
        $this->executeWithFlatEnabledInAdminArea(
            function () {
                $category = $this->getLoadedDefaultCategory();

                $categoryOne = $this->instantiateCategoryModel();
                $categoryOne->setName('Category One')->setPath($category->getPath())->setIsActive(true);
                $category->getResource()->save($categoryOne);
                self::$categoryOne = $categoryOne->getId();

                $categoryTwo = $this->instantiateCategoryModel();
                $categoryTwo->setName('Category Two')->setPath($categoryOne->getPath())->setIsActive(true);
                $category->getResource()->save($categoryTwo);
                self::$categoryTwo = $categoryTwo->getId();
            }
        );
    }

    /**
     * Invoke business logic:
     * - create child category in the Default category
     * - create child category in category created in previous step
     * - move category created on previous step to default category
     */
    private function moveSubCategoriesInDefaultCategory()
    {
        $this->executeWithFlatEnabledInAdminArea(
            function () {
                $this->createSubCategoriesInDefaultCategory();
                $categoryTwo = $this->getLoadedCategory(self::$categoryTwo);
                $categoryTwo->move(self::$defaultCategoryId, self::$categoryOne);
            }
        );
    }

    /**
     * Invoke business logic:
     * - create child category in the Default category
     * - create child category in category created in previous step
     * - delete created categories
     */
    private function deleteSubCategoriesInDefaultCategory()
    {
        $this->executeWithFlatEnabledInAdminArea(
            function () {
                $this->createSubCategoriesInDefaultCategory();
                $this->removeSubCategoriesInDefaultCategory();
            }
        );
    }

    /**
     * Invoke business logic:
     * - delete created categories
     */
    private function removeSubCategoriesInDefaultCategory()
    {
        $this->executeWithFlatEnabledInAdminArea(
            function () {
                $category = $this->instantiateCategoryModel();
                $category->load(self::$categoryTwo);
                $category->delete();
                $category->load(self::$categoryOne);
                $category->delete();
            }
        );
    }

    /**
     * Execute callable in an adminhtml area with enabled flat catalog.
     * After execution area and config option for flat catalog would be restored.
     *
     * @param callable $task
     */
    private function executeWithFlatEnabledInAdminArea(callable $task)
    {
        $app = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()->getApplication();

        $invocationConfigValue = $this->getActiveConfigInstance()->getValue(
            'catalog/frontend/flat_catalog_category',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $invocationArea = $app->getArea();

        $this->switchAppArea($app, 'adminhtml');
        $this->getActiveConfigInstance()->setValue(
            'catalog/frontend/flat_catalog_category',
            true,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        call_user_func($task);
        $this->switchAppArea($app, $invocationArea);
        $this->getActiveConfigInstance()->setValue(
            'catalog/frontend/flat_catalog_category',
            $invocationConfigValue,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Change application area if necessary
     *
     * @param \Magento\TestFramework\Application $app
     * @param string $expectedArea
     */
    private function switchAppArea(\Magento\TestFramework\Application $app, $expectedArea)
    {
        if ($app->getArea() === $expectedArea) {
            return;
        }

        $app->reinitialize();
        if ($app->getArea() === $expectedArea) {
            return;
        }
        $app->loadArea($expectedArea);
    }

    /**
     * Retrieve config object instance that is currently in use by application
     *
     * @return \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    private function getActiveConfigInstance()
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        );
    }
}
