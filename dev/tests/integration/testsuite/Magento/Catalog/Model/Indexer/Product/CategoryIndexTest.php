<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;

/**
 * Checks category products indexing
 *
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryIndexTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var AdapterInterface */
    private $connection;

    /** @var TableMaintainer */
    private $tableMaintainer;

    /** @var ProductResource */
    private $productResource;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CategoryResource */
    private $categoryResource;

    /** @var GetCategoryByName */
    private $getCategoryByName;

    /** @var DefaultCategory */
    private $defaultCategoryHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->connection = $this->productResource->getConnection();
        $this->tableMaintainer = $this->objectManager->get(TableMaintainer::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->categoryResource = $this->objectManager->get(CategoryResource::class);
        $this->getCategoryByName = $this->objectManager->create(GetCategoryByName::class);
        $this->defaultCategoryHelper = $this->objectManager->get(DefaultCategory::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_parent_anchor.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @dataProvider assignCategoriesDataProvider
     *
     * @param string $categoryName
     * @param int $expectedItemsCount
     * @return void
     */
    public function testProductAssignCategory(string $categoryName, int $expectedItemsCount): void
    {
        $product = $this->productRepository->get('simple2');
        $category = $this->getCategoryByName->execute($categoryName);
        $product->setCategoryIds(array_merge($product->getCategoryIds(), [$category->getId()]));
        $this->productResource->save($product);
        $result = $this->getIndexRecordsByProductId((int)$product->getId());
        $this->assertEquals($expectedItemsCount, $result);
    }

    /**
     * @return array
     */
    public function assignCategoriesDataProvider(): array
    {
        return [
            'assign_to_category' => [
                'category_name' => 'Parent category',
                'expected_records_count' => 1,
            ],
            'assign_to_category_with_parent_anchor_category' => [
                'category_name' => 'Child category',
                'expected_records_count' => 2,
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_parent_anchor.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @dataProvider assignProductsDataProvider
     *
     * @param string $categoryName
     * @param int $expectedCount
     * @return void
     */
    public function testCategoryAssignProduct(string $categoryName, int $expectedCount): void
    {
        $product = $this->productRepository->get('simple2');
        $category = $this->getCategoryByName->execute($categoryName);
        $data = ['posted_products' => [$product->getId() => 0]];
        $category->addData($data);
        $this->categoryResource->save($category);
        $result = $this->getIndexRecordsByProductId((int)$product->getId());
        $this->assertEquals($expectedCount, $result);
    }

    /**
     * @return array
     */
    public function assignProductsDataProvider(): array
    {
        return [
            'assign_product_to_category' => [
                'category_name' => 'Parent category',
                'expected_records_count' => 1,
            ],
            'assign_product_to_category_with_parent_anchor_category' => [
                'category_name' => 'Child category',
                'expected_records_count' => 2,
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_product_assigned_to_website.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_parent_anchor.php
     *
     * @return void
     */
    public function testCategoryMove(): void
    {
        $product = $this->productRepository->get('product_with_category');
        $category = $this->getCategoryByName->execute('Category with product');
        $newParentCategory = $this->getCategoryByName->execute('Parent category');
        $afterCategory = $this->getCategoryByName->execute('Child category');
        $category->move($newParentCategory->getId(), $afterCategory->getId());
        $result = $this->getIndexRecordsByProductId((int)$product->getId());
        $this->assertEquals(2, $result);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_product_assigned_to_website.php
     *
     * @return void
     */
    public function testDeleteProduct(): void
    {
        $product = $this->productRepository->get('product_with_category');
        $this->productRepository->delete($product);
        $result = $this->getIndexRecordsByProductId((int)$product->getId());
        $this->assertEmpty($result);
    }

    /**
     * Fetch data from category product index table
     *
     * @param int $productId
     * @return int
     */
    private function getIndexRecordsByProductId(int $productId): int
    {
        $tableName = $this->tableMaintainer->getMainTable((int)$this->storeManager->getStore()->getId());
        $select = $this->connection->select();
        $select->from(['index_table' => $tableName], new \Zend_Db_Expr('COUNT(*)'))
            ->where('index_table.product_id = ?', $productId)
            ->where('index_table.category_id != ?', $this->defaultCategoryHelper->getId());

        return (int)$this->connection->fetchOne($select);
    }
}
