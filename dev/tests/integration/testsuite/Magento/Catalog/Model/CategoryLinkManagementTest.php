<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use PHPUnit\Framework\TestCase;

/**
 * Test cases related to assign/unassign product to/from category.
 */
class CategoryLinkManagementTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CategoryResourceModel
     */
    private $categoryResourceModel;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->tableMaintainer = $this->objectManager->get(TableMaintainer::class);
        $this->storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->categoryResourceModel = $this->objectManager->get(CategoryResourceModel::class);
        $this->categoryLinkManagement = $this->objectManager->create(CategoryLinkManagementInterface::class);
        $this->productRepository->cleanCache();
        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(CategoryLinkRepository::class);
        $this->objectManager->removeSharedInstance(CategoryRepository::class);
        parent::tearDown();
    }

    /**
     * Assert that product correctly assigned to category and index table contain indexed data.
     *
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testAssignProductToCategory(): void
    {
        $product = $this->productRepository->get('simple2');
        $this->assertEquals(0, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [333]));
        $this->assertEquals(0, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [333]));
        $this->categoryLinkManagement->assignProductToCategories('simple2', [333]);
        $this->assertEquals(1, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [333]));
        $this->assertEquals(1, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [333]));
    }

    /**
     * Assert that product correctly unassigned from category and index table not contain indexed data.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_category.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testUnassignProductFromCategory(): void
    {
        $product = $this->productRepository->get('in-stock-product');
        $this->assertEquals(1, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [333]));
        $this->assertEquals(1, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [333]));
        $this->categoryLinkManagement->assignProductToCategories('in-stock-product', []);
        $this->assertEquals(0, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [333]));
        $this->assertEquals(0, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [333]));
    }

    /**
     * Assert that product correctly assigned to category and index table contain index data.
     *
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/categories_no_products.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testAssignProductToCategoryWhichHasParentCategories(): void
    {
        $product = $this->productRepository->get('simple2');
        $this->assertEquals(0, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [5]));
        $this->assertEquals(0, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [3, 4, 5]));
        $this->categoryLinkManagement->assignProductToCategories('simple2', [5]);
        $this->assertEquals(1, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [5]));
        $this->assertEquals(3, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [3, 4, 5]));
    }

    /**
     * Assert that product correctly unassigned from category and index table doesn't contain index data.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_category_which_has_parent_category.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testUnassignProductFromCategoryWhichHasParentCategories(): void
    {
        $product = $this->productRepository->get('simple_with_child_category');
        $this->assertEquals(1, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [5]));
        $this->assertEquals(3, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [3, 4, 5]));
        $this->categoryLinkManagement->assignProductToCategories('simple_with_child_category', []);
        $this->assertEquals(0, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [5]));
        $this->assertEquals(0, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [3, 4, 5]));
    }

    /**
     * Assert that product correctly reassigned to another category.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_category_which_has_parent_category.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testReassignProductToOtherCategory(): void
    {
        $product = $this->productRepository->get('simple_with_child_category');
        $this->assertEquals(1, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [5]));
        $this->assertEquals(3, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [3, 4, 5]));
        $this->categoryLinkManagement->assignProductToCategories('simple_with_child_category', [6]);
        $this->assertEquals(1, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [6]));
        $this->assertEquals(1, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [6]));
        $this->assertEquals(0, $this->getCategoryProductRelationRecordsCount((int)$product->getId(), [5]));
        $this->assertEquals(0, $this->getCategoryProductIndexRecordsCount((int)$product->getId(), [3, 4, 5]));
    }

    /**
     * Return count of product which assigned to provided categories.
     *
     * @param int $productId
     * @param int[] $categoryIds
     * @return int
     */
    private function getCategoryProductRelationRecordsCount(int $productId, array $categoryIds): int
    {
        $select = $this->categoryResourceModel->getConnection()->select();
        $select->from(
            $this->categoryResourceModel->getCategoryProductTable(),
            [
                'row_count' => new \Zend_Db_Expr('COUNT(*)')
            ]
        );
        $select->where('product_id = ?', $productId);
        $select->where('category_id IN (?)', $categoryIds);

        return (int)$this->categoryResourceModel->getConnection()->fetchOne($select);
    }

    /**
     * Return count of products which added to index table with all provided category ids.
     *
     * @param int $productId
     * @param array $categoryIds
     * @param string $storeCode
     * @return int
     */
    private function getCategoryProductIndexRecordsCount(
        int $productId,
        array $categoryIds,
        string $storeCode = 'default'
    ): int {
        $storeId = (int)$this->storeRepository->get($storeCode)->getId();
        $select = $this->categoryResourceModel->getConnection()->select();
        $select->from(
            $this->tableMaintainer->getMainTable($storeId),
            [
                'row_count' => new \Zend_Db_Expr('COUNT(*)')
            ]
        );
        $select->where('product_id = ?', $productId);
        $select->where('category_id IN (?)', $categoryIds);

        return (int)$this->categoryResourceModel->getConnection()->fetchOne($select);
    }
}
