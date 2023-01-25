<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Category;

use Magento\Catalog\Model\Category;

/**
 * @magentoAppIsolation enabled
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

    protected function setUp(): void
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
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/fixed_bundle_product.php
     * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
     * @magentoDbIsolation disabled
     */
    public function testReindex()
    {
        $categories = $this->getCategories();

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);
        /** @var \Magento\Catalog\Model\Product $bundleProduct */
        $bundleProduct = $this->productRepository->get('bundle_product');
        $bundleProduct->setCategoryIds([$categoryFourth->getId()]);
        $this->productRepository->save($bundleProduct);

        /** @var Category $categoryThird */
        $categoryThird = $categories[2];
        $categoryThird->setIsAnchor(true);
        $this->categoryRepository->save($categoryThird);

        $this->indexer->reindexAll();

        $categories = [self::DEFAULT_ROOT_CATEGORY, $categoryThird->getId(), $categoryFourth->getId()];
        foreach ($categories as $categoryId) {
            $this->assertTrue((bool)$this->productResource->canBeShowInCategory($bundleProduct, $categoryId));
        }

        $this->assertTrue(
            (bool)$this->productResource->canBeShowInCategory($bundleProduct, $categoryThird->getParentId())
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/fixed_bundle_product.php
     * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
     * @magentoDbIsolation disabled
     */
    public function testCategoryMove()
    {
        $categories = $this->getCategories();

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);
        $bundleProduct = $this->productRepository->get('bundle_product');
        $bundleProduct->setCategoryIds([$categoryFourth->getId()]);
        $this->productRepository->save($bundleProduct);

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
            $this->assertTrue((bool)$this->productResource->canBeShowInCategory($bundleProduct, $categoryId));
        }

        $this->assertFalse(
            (bool)$this->productResource->canBeShowInCategory($bundleProduct, $categoryThird->getId())
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product.php
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/fixed_bundle_product.php
     * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
     * @depends testReindex
     * @magentoDbIsolation disabled
     */
    public function testCategoryDelete()
    {
        $categories = $this->getCategories();
        $bundleProduct = $this->productRepository->get('bundle_product');

        /** @var Category $categoryFourth */
        $categoryFourth = end($categories);
        $this->categoryRepository->delete($categoryFourth);

        /** @var Category $categorySecond */
        $categorySecond = $categories[1];

        $categories = [$categorySecond->getId(), $categoryFourth->getId()];

        foreach ($categories as $categoryId) {
            $this->assertFalse((bool)$this->productResource->canBeShowInCategory($bundleProduct, $categoryId));
        }
        $this->assertTrue(
            (bool)$this->productResource->canBeShowInCategory(
                $bundleProduct,
                self::DEFAULT_ROOT_CATEGORY
            )
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product.php
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/fixed_bundle_product.php
     * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_category.php
     * @magentoDbIsolation disabled
     */
    public function testCategoryCreate()
    {
        $this->testReindex();
        $categories = $this->getCategories();
        $bundleProduct = $this->productRepository->get('bundle_product');

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

        $bundleProduct->setCategoryIds([$categorySixth->getId()]);
        $bundleProduct->save();

        $categories = [self::DEFAULT_ROOT_CATEGORY, $categorySixth->getId(), $categoryFourth->getId()];
        foreach ($categories as $categoryId) {
            $this->assertTrue((bool)$this->productResource->canBeShowInCategory($bundleProduct, $categoryId));
        }

        $this->assertFalse(
            (bool)$this->productResource->canBeShowInCategory($bundleProduct, $categorySecond->getId())
        );
    }

    /**
     * Finds 4 categories
     *
     * @return Category[]
     */
    private function getCategories()
    {
        $collectionFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class
        );

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $collectionFactory->create();

        $collection
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('name', ['in' => [
                'Category 1',
                'Category 2',
                'Category 3',
                'Category 4',
            ]]);

        return array_values($collection->getItems());
    }
}
