<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category;

use Magento\Catalog\Model\Category;

/**
 * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
 * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_products.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_ROOT_CATEGORY = 2;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    protected function setUp()
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface indexer */
        $this->indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Indexer\Model\Indexer::class
        );
        $this->indexer->load('catalog_category_product');

        /** @var \Magento\Catalog\Model\ResourceModel\Product $productResource */
        $this->productResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\ResourceModel\Product::class
        );
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
            /** @var \Magento\Catalog\Model\Product $product */
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
            /** @var \Magento\Catalog\Model\Product $product */
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
            /** @var \Magento\Catalog\Model\Product $product */
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
            /** @var \Magento\Catalog\Model\Product $product */
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
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($categories as $categoryId) {
                $this->assertFalse((bool)$this->productResource->canBeShowInCategory($product, $categoryId));
            }
            $this->assertTrue(
                (bool)$this->productResource->canBeShowInCategory($product, self::DEFAULT_ROOT_CATEGORY)
            );
        }
    }

    public function testCategoryCreate()
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
        $categorySixth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
        $categorySixth->setName(
            'Category 6'
        )->setPath(
            $categoryFourth->getPath()
        )->setAvailableSortBy(
            'name'
        )->setDefaultSortBy(
            'name'
        )->setIsActive(
            true
        )->save();

        /** @var \Magento\Catalog\Model\Product $productThird */
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
     * @param int $count
     * @return Category[]
     */
    protected function getCategories($count)
    {
        /** @var Category $category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );

        $result = $category->getCollection()->addAttributeToSelect('name')->getItems();
        $result = array_slice($result, 2);

        return array_slice($result, 0, $count);
    }

    /**
     * @param int $count
     * @return \Magento\Catalog\Model\Product[]
     */
    protected function getProducts($count)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );

        $result[] = $product->load(1);
        $result[] = $product->load(2);
        $result[] = $product->load(3);

        return array_slice($result, 0, $count);
    }

    /**
     * Clear index data
     */
    protected function clearIndex()
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
}
