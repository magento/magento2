<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Category;

/**
 * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
 * @magentoDbIsolation enabled
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_ROOT_CATEGORY = 2;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $productResource;

    protected function setUp()
    {
        /** @var \Magento\Indexer\Model\IndexerInterface indexer */
        $this->indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Indexer\Model\Indexer'
        );
        $this->indexer->load('catalog_category_product');

        /** @var \Magento\Catalog\Model\Resource\Product $productResource */
        $this->productResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Resource\Product'
        );
    }

    public function testReindexAll()
    {
        $categories = $this->getCategories(4);
        $products = $this->getProducts(2);

        /** @var \Magento\Catalog\Model\Category $categoryFourth */
        $categoryFourth = end($categories);
        foreach ($products as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product->setCategoryIds(array($categoryFourth->getId()));
            $product->save();
        }

        /** @var \Magento\Catalog\Model\Category $categoryThird */
        $categoryThird = $categories[2];
        $categoryThird->setIsAnchor(true);
        $categoryThird->save();

        $this->clearIndex();
        $categories = array(self::DEFAULT_ROOT_CATEGORY, $categoryThird->getId(), $categoryFourth->getId());

        $this->indexer->reindexAll();

        foreach ($products as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($categories as $categoryId) {
                $this->assertTrue((bool)$this->productResource->canBeShowInCategory($product, $categoryId));
            }

            $this->assertFalse(
                (bool)$this->productResource->canBeShowInCategory($product, $categoryThird->getParentId())
            );
        }
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories.php
     * @magentoAppIsolation enabled
     * @depends testReindexAll
     */
    public function testCategoryMove()
    {
        $categories = $this->getCategories(4);
        $products = $this->getProducts(2);

        /** @var \Magento\Catalog\Model\Category $categoryFourth */
        $categoryFourth = end($categories);

        /** @var \Magento\Catalog\Model\Category $categorySecond */
        $categorySecond = $categories[1];
        $categorySecond->setIsAnchor(true);
        $categorySecond->save();

        /** @var \Magento\Catalog\Model\Category $categoryThird */
        $categoryThird = $categories[2];

        /**
         * Move category from $categoryThird to $categorySecond
         */
        $categoryFourth->move($categorySecond->getId(), null);

        $categories = array(self::DEFAULT_ROOT_CATEGORY, $categorySecond->getId(), $categoryFourth->getId());

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

        /** @var \Magento\Catalog\Model\Category $categoryFourth */
        $categoryFourth = end($categories);
        $categoryFourth->delete();

        /** @var \Magento\Catalog\Model\Category $categorySecond */
        $categorySecond = $categories[1];

        $categories = array($categorySecond->getId(), $categoryFourth->getId());

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

    /**
     * @depends testReindexAll
     */
    public function testCategoryCreate()
    {
        $categories = $this->getCategories(4);
        $products = $this->getProducts(3);

        /** @var \Magento\Catalog\Model\Category $categorySecond */
        $categorySecond = $categories[1];
        $categorySecond->setIsAnchor(0);
        $categorySecond->save();

        /** @var \Magento\Catalog\Model\Category $categoryFifth */
        $categoryFifth = end($categories);

        /** @var \Magento\Catalog\Model\Category $categorySixth */
        $categorySixth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );
        $categorySixth->setName(
            'Category 6'
        )->setPath(
            $categoryFifth->getPath()
        )->setAvailableSortBy(
            'name'
        )->setDefaultSortBy(
            'name'
        )->setIsActive(
            true
        )->save();

        /** @var \Magento\Catalog\Model\Product $productThird */
        $productThird = end($products);
        $productThird->setCategoryIds(array($categorySixth->getId()));
        $productThird->save();

        $categories = array(self::DEFAULT_ROOT_CATEGORY, $categorySixth->getId());
        foreach ($categories as $categoryId) {
            $this->assertTrue((bool)$this->productResource->canBeShowInCategory($productThird, $categoryId));
        }

        $categories = array($categoryFifth->getId(), $categorySecond->getId());
        foreach ($categories as $categoryId) {
            $this->assertFalse((bool)$this->productResource->canBeShowInCategory($productThird, $categoryId));
        }
    }

    /**
     * @param int $count
     * @return \Magento\Catalog\Model\Category[]
     */
    protected function getCategories($count)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );

        $result = $category->getCollection()->getItems();
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
            'Magento\Catalog\Model\Product'
        );

        $result = $product->getCollection()->getItems();

        return array_slice($result, 0, $count);
    }

    /**
     * Clear index data
     */
    protected function clearIndex()
    {
        $this->productResource->getWriteConnection()->delete(
            $this->productResource->getTable('catalog_category_product_index')
        );

        $actualResult = $this->productResource->getReadConnection()->fetchOne(
            $this->productResource->getReadConnection()->select()->from(
                $this->productResource->getTable('catalog_category_product_index'),
                'product_id'
            )
        );
        $this->assertFalse($actualResult);
    }
}
