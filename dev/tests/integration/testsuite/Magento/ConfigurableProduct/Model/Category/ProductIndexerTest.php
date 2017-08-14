<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Category;

use Magento\Catalog\Model\Category;

/**
 * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ProductIndexerTest extends \PHPUnit\Framework\TestCase
{
    const DEFAULT_ROOT_CATEGORY = 2;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    private $indexer;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    protected function setUp()
    {
        $this->indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Indexer\Model\Indexer::class
        );
        $this->indexer->load('catalog_category_product');

        $this->productResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\ResourceModel\Product::class
        );
        $this->productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $this->categoryRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\CategoryRepository::class
        );
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testReindex()
    {
        $categories = $this->getCategories();

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);
        /** @var \Magento\Catalog\Model\Product $configurableProduct */
        $configurableProduct = $this->productRepository->get('configurable');
        $configurableProduct->setCategoryIds([$categoryFourth->getId()]);
        $this->productRepository->save($configurableProduct);

        /** @var Category $categoryThird */
        $categoryThird = $categories[2];
        $categoryThird->setIsAnchor(true);
        $this->categoryRepository->save($categoryThird);

        $this->indexer->reindexAll();

        $categories = [self::DEFAULT_ROOT_CATEGORY, $categoryThird->getId(), $categoryFourth->getId()];
        foreach ($categories as $categoryId) {
            $this->assertTrue((bool)$this->productResource->canBeShowInCategory($configurableProduct, $categoryId));
        }

        $this->assertTrue(
            (bool)$this->productResource->canBeShowInCategory($configurableProduct, $categoryThird->getParentId())
        );
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testCategoryMove()
    {
        $categories = $this->getCategories();

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);
        $configurableProduct = $this->productRepository->get('configurable');
        $configurableProduct->setCategoryIds([$categoryFourth->getId()]);
        $this->productRepository->save($configurableProduct);

        /** @var Category $categorySecond */
        $categorySecond = $categories[1];
        $categorySecond->setIsAnchor(true);
        $this->categoryRepository->save($categorySecond);

        /** @var Category $categoryThird */
        $categoryThird = $categories[2];
        $categoryThird->setIsAnchor(true);
        $this->categoryRepository->save($categoryThird);

        $this->indexer->reindexAll();

        /**
         * Move category from $categoryThird to $categorySecond
         */
        $categoryFourth->move($categorySecond->getId(), null);

        $categories = [self::DEFAULT_ROOT_CATEGORY, $categorySecond->getId(), $categoryFourth->getId()];

        foreach ($categories as $categoryId) {
            $this->assertTrue((bool)$this->productResource->canBeShowInCategory($configurableProduct, $categoryId));
        }

        $this->assertFalse(
            (bool)$this->productResource->canBeShowInCategory($configurableProduct, $categoryThird->getId())
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @depends testReindex
     */
    public function testCategoryDelete()
    {
        $categories = $this->getCategories();
        $configurableProduct = $this->productRepository->get('configurable');

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);
        $this->categoryRepository->delete($categoryFourth);

        /** @var Category $categorySecond */
        $categorySecond = $categories[1];

        $categories = [$categorySecond->getId(), $categoryFourth->getId()];

        foreach ($categories as $categoryId) {
            $this->assertFalse((bool)$this->productResource->canBeShowInCategory($configurableProduct, $categoryId));
        }
        $this->assertTrue(
            (bool)$this->productResource->canBeShowInCategory(
                $configurableProduct,
                self::DEFAULT_ROOT_CATEGORY
            )
        );
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testCategoryCreate()
    {
        $this->testReindex();
        $categories = $this->getCategories();
        $configurableProduct = $this->productRepository->get('configurable');

        /** @var Category $categorySecond */
        $categorySecond = $categories[1];
        $categorySecond->setIsAnchor(0);
        $this->categoryRepository->save($categorySecond);

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
        );
        $this->categoryRepository->save($categorySixth);

        $configurableProduct->setCategoryIds([$categorySixth->getId()]);
        $configurableProduct->save();

        $categories = [self::DEFAULT_ROOT_CATEGORY, $categorySixth->getId(), $categoryFourth->getId()];
        foreach ($categories as $categoryId) {
            $this->assertTrue((bool)$this->productResource->canBeShowInCategory($configurableProduct, $categoryId));
        }

        $this->assertFalse(
            (bool)$this->productResource->canBeShowInCategory($configurableProduct, $categorySecond->getId())
        );
    }

    /**
     * @return Category[]
     */
    private function getCategories()
    {
        /** @var Category $category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );

        $result = $category->getCollection()->addAttributeToSelect('name')->getItems();
        $result = array_slice($result, 2);

        return array_slice($result, 0, 4);
    }
}
