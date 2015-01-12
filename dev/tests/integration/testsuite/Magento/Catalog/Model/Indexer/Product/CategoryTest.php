<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

/**
 * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
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
        $this->indexer->load('catalog_product_category');

        /** @var \Magento\Catalog\Model\Resource\Product $productResource */
        $this->productResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Resource\Product'
        );
    }

    public function testReindexAll()
    {
        $categories = $this->getCategories(4);
        $products = $this->getProducts(3);

        /** @var \Magento\Catalog\Model\Category $categoryFourth */
        $categoryFourth = end($categories);
        foreach ($products as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product->setCategoryIds([$categoryFourth->getId()]);
            $product->save();
        }

        /** @var \Magento\Catalog\Model\Category $categoryFirst */
        $categoryFirst = $categories[0];
        $categoryFirst->setIsAnchor(true);
        $categoryFirst->save();

        $this->clearIndex();
        $categories = [self::DEFAULT_ROOT_CATEGORY, $categoryFirst->getId(), $categoryFourth->getId()];

        $this->indexer->reindexAll();

        foreach ($products as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($categories as $categoryId) {
                $this->assertTrue((bool)$this->productResource->canBeShowInCategory($product, $categoryId));
            }
        }
    }

    /**
     * @depends testReindexAll
     */
    public function testStatusChange()
    {
        $categories = $this->getCategories(4);
        $products = $this->getProducts(3);

        /** @var \Magento\Catalog\Model\Product $product */
        $productFirst = array_shift($products);
        $productFirst->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        $productFirst->save();

        /** @var \Magento\Catalog\Model\Category $categoryFirst */
        $categoryFirst = $categories[0];

        /** @var \Magento\Catalog\Model\Category $categoryFourth */
        $categoryFourth = end($categories);

        $categories = [self::DEFAULT_ROOT_CATEGORY, $categoryFirst->getId(), $categoryFourth->getId()];

        foreach ($categories as $categoryId) {
            foreach ($products as $product) {
                /** @var \Magento\Catalog\Model\Product $product */
                $this->assertTrue((bool)$this->productResource->canBeShowInCategory($product, $categoryId));
            }
            $this->assertFalse((bool)$this->productResource->canBeShowInCategory($productFirst, $categoryId));
        }
    }

    /**
     * @depends testReindexAll
     */
    public function testVisibilityChange()
    {
        $categories = $this->getCategories(4);
        $products = $this->getProducts(3);

        /** @var \Magento\Catalog\Model\Product $product */
        $productFirst = $products[0];
        $productFirst->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $productFirst->save();

        $productThird = array_pop($products);
        $productThird->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        $productThird->save();

        /** @var \Magento\Catalog\Model\Category $categoryFirst */
        $categoryFirst = $categories[0];

        /** @var \Magento\Catalog\Model\Category $categoryFourth */
        $categoryFourth = end($categories);

        $categories = [self::DEFAULT_ROOT_CATEGORY, $categoryFirst->getId(), $categoryFourth->getId()];

        foreach ($categories as $categoryId) {
            foreach ($products as $product) {
                /** @var \Magento\Catalog\Model\Product $product */
                $this->assertTrue((bool)$this->productResource->canBeShowInCategory($product, $categoryId));
            }
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
