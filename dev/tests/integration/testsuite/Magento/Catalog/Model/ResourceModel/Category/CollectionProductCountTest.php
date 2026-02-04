<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for category product count functionality.
 *
 * Tests the fix for magento/magento2#40263 - product count shows 0 for
 * anchor categories without children (leaf anchor categories).
 *
 * @see \Magento\Catalog\Model\ResourceModel\Category\Collection::loadProductCount()
 * @see \Magento\Catalog\Model\ResourceModel\Category\Collection::getCountFromCategoryTableBulk()
 */
class CollectionProductCountTest extends TestCase
{
    private ObjectManagerInterface $objectManager;
    private CollectionFactory $collectionFactory;
    private CategoryRepositoryInterface $categoryRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->collectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
    }

    /**
     * Test that anchor categories without children correctly count products assigned directly to them.
     *
     * This is the main regression test for magento/magento2#40263.
     * Without the fix, leaf anchor categories would show 0 products.
     *
     * @magentoDataFixture Magento/Catalog/_files/category_anchor_without_children_with_product.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testLeafAnchorCategoryProductCount(): void
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addIdFilter([555, 556]);
        $collection->setLoadProductCount(true);
        $collection->load();

        /** @var Category $category555 */
        $category555 = $collection->getItemById(555);
        /** @var Category $category556 */
        $category556 = $collection->getItemById(556);

        $this->assertNotNull($category555, 'Category 555 should exist');
        $this->assertNotNull($category556, 'Category 556 should exist');

        // Without the fix, these would be 0
        $this->assertEquals(
            1,
            $category555->getProductCount(),
            'Leaf anchor category 555 should count 1 product assigned directly to it'
        );
        $this->assertEquals(
            1,
            $category556->getProductCount(),
            'Leaf anchor category 556 should count 1 product assigned directly to it'
        );
    }

    /**
     * Test that the bulk processing method correctly includes self-references without causing duplicate entry errors.
     *
     * This tests that the fix doesn't reintroduce the ACP2E-4159 regression (duplicate entry errors).
     *
     * @magentoDataFixture Magento/Catalog/_files/category_anchor_without_children_with_product.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testBulkQueryDoesNotCauseDuplicateEntryError(): void
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        /** @var AdapterInterface $connection */
        $connection = $collection->getConnection();

        $categoryIds = [555, 556];
        $tempTableName = 'temp_category_descendants_test_' . uniqid();

        try {
            // Create temp table matching the structure in getCountFromCategoryTableBulk
            $tempTable = $connection->newTable($tempTableName)
                ->addColumn(
                    'category_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Category ID'
                )
                ->addColumn(
                    'descendant_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Descendant ID'
                )
                ->addIndex(
                    $connection->getIndexName($tempTableName, ['category_id', 'descendant_id']),
                    ['category_id', 'descendant_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
                );
            $connection->createTemporaryTable($tempTable);

            // Build the SELECT with the fix (includes self-reference via OR condition)
            $selectDescendants = $connection->select()
                ->from(
                    ['ce' => $collection->getTable('catalog_category_entity')],
                    ['category_id' => 'ce.entity_id', 'descendant_id' => 'ce2.entity_id']
                )
                ->joinInner(
                    ['ce2' => $collection->getTable('catalog_category_entity')],
                    'ce2.path LIKE CONCAT(ce.path, \'/%\') OR ce2.entity_id = ce.entity_id',
                    []
                )
                ->where('ce.entity_id IN (?)', $categoryIds);

            // This should NOT throw a duplicate entry exception
            $connection->query(
                $connection->insertFromSelect(
                    $selectDescendants,
                    $tempTableName,
                    ['category_id', 'descendant_id']
                )
            );

            // Verify self-references were inserted
            $rows = $connection->fetchAll("SELECT * FROM {$tempTableName} ORDER BY category_id, descendant_id");

            $selfRefCount = 0;
            foreach ($rows as $row) {
                if ($row['category_id'] === $row['descendant_id']) {
                    $selfRefCount++;
                }
            }

            $this->assertEquals(
                count($categoryIds),
                $selfRefCount,
                'Each category should have a self-reference entry in the temp table'
            );
        } finally {
            $connection->dropTemporaryTable($tempTableName);
        }
    }

    /**
     * Test that the LIKE condition and self-reference condition are mutually exclusive.
     *
     * This validates our assumption that duplicates cannot occur because:
     * - 'path/%' never matches the category's own path (LIKE requires chars after /)
     * - Self-reference only matches the category itself
     *
     * @magentoDataFixture Magento/Catalog/_files/category_anchor_without_children_with_product.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testLikeAndSelfReferenceAreMutuallyExclusive(): void
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        /** @var AdapterInterface $connection */
        $connection = $collection->getConnection();

        // Test with category 555 (path: 1/2/555)
        $category = $this->categoryRepository->get(555);
        $categoryPath = $category->getPath();

        // Query to find rows that match BOTH conditions (should be 0)
        $select = $connection->select()
            ->from(
                ['ce' => $collection->getTable('catalog_category_entity')],
                ['count' => 'COUNT(*)']
            )
            ->joinInner(
                ['ce2' => $collection->getTable('catalog_category_entity')],
                // Both conditions must be true (not OR)
                'ce2.path LIKE CONCAT(ce.path, \'/%\') AND ce2.entity_id = ce.entity_id',
                []
            )
            ->where('ce.entity_id = ?', 555);

        $count = (int) $connection->fetchOne($select);

        $this->assertEquals(
            0,
            $count,
            'LIKE and self-reference conditions should be mutually exclusive - a category cannot be its own descendant'
        );
    }

    /**
     * Test product count for a parent category with children (traditional scenario).
     *
     * This ensures the fix doesn't break the existing behavior for categories with children.
     *
     * @magentoDataFixture Magento/Catalog/_files/category_anchor.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testParentAnchorCategoryProductCount(): void
    {
        // Category 22 is an anchor with child category 11
        // Product1 is assigned to category 11 (child)
        // Product2 is assigned to category 22 (parent)

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addIdFilter([22]);
        $collection->setLoadProductCount(true);
        $collection->load();

        /** @var Category $parentCategory */
        $parentCategory = $collection->getItemById(22);

        $this->assertNotNull($parentCategory, 'Parent anchor category 22 should exist');

        // Parent anchor category should count products from itself AND its children
        // Product1 (in child 11) + Product2 (in parent 22) = 2 products
        $this->assertGreaterThanOrEqual(
            2,
            $parentCategory->getProductCount(),
            'Parent anchor category should count products from itself and descendants'
        );
    }
}
