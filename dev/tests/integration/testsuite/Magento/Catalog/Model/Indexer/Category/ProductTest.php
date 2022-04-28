<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for catalog_category_product indexer.
 *
 * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
 * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_products.php
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends TestCase
{
    private const DEFAULT_ROOT_CATEGORY = 2;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var GetCategoryByName
     */
    private $getCategoryByName;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        /** @var IndexerInterface indexer */
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->indexer->load(CategoryProductIndexer::INDEXER_ID);

        $this->productResource = Bootstrap::getObjectManager()->get(ProductResource::class);
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->categoryResource = Bootstrap::getObjectManager()->create(CategoryResource::class);
        $this->getCategoryByName = Bootstrap::getObjectManager()->create(GetCategoryByName::class);
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testReindexAll()
    {
        $categories = $this->getCategories(4);
        $products = $this->getProducts(2);

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);
        foreach ($products as $product) {
            /** @var ProductModel $product */
            $product->setCategoryIds([$categoryFourth->getId()]);
            $product->save();
        }

        /** @var Category $categoryThird */
        $categoryThird = $categories[2];
        $categoryThird->setIsAnchor(true);
        $categoryThird->save();

        $this->clearIndex();
        $categories = [self::DEFAULT_ROOT_CATEGORY, $categoryThird->getId(), $categoryFourth->getId()];

        $this->indexer->reindexAll();

        foreach ($products as $product) {
            /** @var ProductModel $product */
            foreach ($categories as $categoryId) {
                $this->assertTrue((bool)$this->productResource->canBeShowInCategory($product, $categoryId));
            }

            $this->assertTrue(
                (bool)$this->productResource->canBeShowInCategory($product, $categoryThird->getParentId())
            );
        }
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testCategoryMove()
    {
        $categories = $this->getCategories(4);
        $products = $this->getProducts(2);

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);
        foreach ($products as $product) {
            /** @var ProductModel $product */
            $product->setCategoryIds([$categoryFourth->getId()]);
            $product->save();
        }

        /** @var Category $categorySecond */
        $categorySecond = $categories[1];
        $categorySecond->setIsAnchor(true);
        $categorySecond->save();

        /** @var Category $categoryThird */
        $categoryThird = $categories[2];
        $categoryThird->setIsAnchor(true);
        $categoryThird->save();

        $this->clearIndex();
        $this->indexer->reindexAll();

        /**
         * Move $categoryFourth from $categoryThird to $categorySecond
         */
        $categoryFourth->move($categorySecond->getId(), null);

        $categories = [self::DEFAULT_ROOT_CATEGORY, $categorySecond->getId(), $categoryFourth->getId()];

        foreach ($products as $product) {
            /** @var ProductModel $product */
            foreach ($categories as $categoryId) {
                $this->assertTrue((bool)$this->productResource->canBeShowInCategory($product, $categoryId));
            }

            $this->assertFalse((bool)$this->productResource->canBeShowInCategory($product, $categoryThird->getId()));
        }
    }

    /**
     * @magentoAppArea adminhtml
     * @depends testReindexAll
     */
    public function testCategoryDelete()
    {
        $categories = $this->getCategories(4);
        $products = $this->getProducts(2);

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);
        $categoryFourth->delete();

        /** @var Category $categorySecond */
        $categorySecond = $categories[1];

        $categories = [$categorySecond->getId(), $categoryFourth->getId()];

        foreach ($products as $product) {
            /** @var ProductModel $product */
            foreach ($categories as $categoryId) {
                $this->assertFalse((bool)$this->productResource->canBeShowInCategory($product, $categoryId));
            }
            $this->assertTrue(
                (bool)$this->productResource->canBeShowInCategory($product, self::DEFAULT_ROOT_CATEGORY)
            );
        }
    }


    /**
     * Verify that indexer still valid after deleting inactive category
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/categories_disabled.php
     *
     * @return void
     */
    public function testDeleteInactiveCategory(): void
    {
        $this->indexer->reindexAll();
        $isInvalidIndexer = $this->indexer->isInvalid();

        $this->categoryRepository->deleteByIdentifier(8);

        $state = $this->indexer->getState();
        $state->loadByIndexer($this->indexer->getId());
        $status = $state->getStatus();

        $this->assertFalse($isInvalidIndexer);
        $this->assertEquals(StateInterface::STATUS_VALID, $status);
    }

    /**
     * Create category
     *
     * @return void
     */
    public function testCategoryCreate(): void
    {
        $this->testReindexAll();
        $categories = $this->getCategories(4);
        $products = $this->getProducts(3);

        /** @var Category $categorySecond */
        $categorySecond = $categories[1];
        $categorySecond->setIsAnchor(0);
        $categorySecond->save();

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);

        /** @var Category $categorySixth */
        $categorySixth = Bootstrap::getObjectManager()->create(Category::class);
        $categorySixth->setName('Category 6')
            ->setPath($categoryFourth->getPath())
            ->setAvailableSortBy('name')
            ->setDefaultSortBy('name')
            ->setIsActive(true)
            ->save();

        /** @var ProductModel $productThird */
        $productThird = end($products);
        $productThird->setCategoryIds([$categorySixth->getId()]);
        $productThird->save();

        $categories = [self::DEFAULT_ROOT_CATEGORY, $categorySixth->getId(), $categoryFourth->getId()];
        foreach ($categories as $categoryId) {
            $this->assertTrue((bool)$this->productResource->canBeShowInCategory($productThird, $categoryId));
        }

        $categories = [$categorySecond->getId()];
        foreach ($categories as $categoryId) {
            $this->assertFalse((bool)$this->productResource->canBeShowInCategory($productThird, $categoryId));
        }
    }

    /**
     * @magentoAppArea adminhtml
     *
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     *
     * @return void
     */
    public function testCatalogCategoryProductIndexInvalidateAfterDelete(): void
    {
        $this->indexer->reindexAll();
        $indexerShouldBeValid = $this->indexer->isInvalid();

        $this->categoryRepository->deleteByIdentifier(400);

        $state = $this->indexer->getState();
        $state->loadByIndexer($this->indexer->getId());
        $status = $state->getStatus();

        $this->assertFalse($indexerShouldBeValid);
        $this->assertEquals(StateInterface::STATUS_INVALID, $status);
    }

    /**
     * @param int $count
     * @return Category[]
     */
    private function getCategories(int $count): array
    {
        /** @var Category $category */
        $category = Bootstrap::getObjectManager()->create(Category::class);

        $result = $category->getCollection()->addAttributeToSelect('name')->getItems();
        $result = array_slice($result, 2);

        return array_slice($result, 0, $count);
    }

    /**
     * @param int $count
     * @return ProductModel[]
     */
    private function getProducts(int $count): array
    {
        /** @var ProductModel $product */
        $product = Bootstrap::getObjectManager()->create(ProductModel::class);

        $result[] = $product->load(1);
        $result[] = $product->load(2);
        $result[] = $product->load(3);

        return array_slice($result, 0, $count);
    }

    /**
     * Clear index data
     */
    private function clearIndex()
    {
        $this->productResource->getConnection()->delete(
            $this->productResource->getTable('catalog_category_product_index')
        );

        $actualResult = $this->productResource->getConnection()->fetchOne(
            $this->productResource->getConnection()->select()->from(
                $this->productResource->getTable('catalog_category_product_index'),
                'product_id'
            )
        );
        $this->assertFalse($actualResult);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->indexer->reindexAll();
        $this->indexer->setScheduled(false);
    }
}
