<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Indexer\Model\Indexer;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_product_categories.php
 * @magentoDataFixture Magento/Catalog/_files/indexer_catalog_products.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CategoryTest extends TestCase
{
    const DEFAULT_ROOT_CATEGORY = 2;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var ProductResource
     */
    private $productResource;

    protected function setUp()
    {
        /** @var Indexer indexer */
        $this->indexer = Bootstrap::getObjectManager()->create(
            Indexer::class
        );
        $this->indexer->load('catalog_product_category');
        /** @var ProductResource $productResource */
        $this->productResource = Bootstrap::getObjectManager()->get(
            ProductResource::class
        );
    }

    /**
     * Check that given product is only visible in given categories.
     *
     * @param Product $product
     * @param Category[] $categoriesIn Categories the product is supposed
     *                                 to be in.
     * @param Category[]   $categories Whole list of categories.
     *
     * @return void
     */
    private function assertProductIn(
        Product $product,
        array $categoriesIn,
        array $categories
    ) {
        foreach ($categories as $category) {
            $visible = in_array($category, $categoriesIn, true);
            $this->assertEquals(
                $visible,
                (bool)$this->productResource->canBeShowInCategory(
                    $product,
                    $category->getId()
                ),
                'Product "' .$product->getName() .'" is'
                .($visible? '' : ' not') .' supposed to be in category "'
                .$category->getName() .'"'
            );
        }
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     */
    public function testReindexAll()
    {
        //Category #1 is base category for this case, Category #2 is non-anchor
        //sub-category of Category #1, Category #3 is anchor sub-category of
        //Category #2, Category #4 is anchor sub-category of Category #1
        //Products are not yet assigned to categories
        $categories = $this->loadCategories(4);
        $products = $this->loadProducts(3);

        //Leaving Product #1 unassigned, Product #2 is assigned to Category #3,
        //Product #3 assigned to Category #3 and #4.
        $products[0]->setCategoryIds(null);
        $this->productResource->save($products[0]);
        $products[1]->setCategoryIds([$categories[2]->getId()]);
        $this->productResource->save($products[1]);
        $products[2]->setCategoryIds([
            $categories[2]->getId(),
            $categories[3]->getId(),
        ]);
        $this->productResource->save($products[2]);
        //Reindexing
        $this->clearIndex();
        $this->indexer->reindexAll();

        //Checking that Category #1 shows only Product #2 and #3 since
        //Product #1 is not assigned to any category, Product #2 is assigned to
        //it's sub-subcategory and Product #3 is assigned to a sub-subcategory
        //and a subcategory.
        //Category #2 doesn't have any products on display because while it's
        //sub-category has products it's a non-anchor category.
        //Category #3 has 2 products directly assigned to it.
        //Category #4 only has 1 product directly assigned to it.
        $this->assertProductIn($products[0], [], $categories);
        $this->assertProductIn(
            $products[1],
            [$categories[0],$categories[2]],
            $categories
        );
        $this->assertProductIn(
            $products[2],
            [$categories[0], $categories[2], $categories[3]],
            $categories
        );

        //Reassigning products a bit
        $products[0]->setCategoryIds([$categories[0]->getId()]);
        $this->productResource->save($products[0]);
        $products[1]->setCategoryIds([]);
        $this->productResource->save($products[1]);
        $products[2]->setCategoryIds([
            $categories[1]->getId(),
            $categories[2]->getId(),
            $categories[3]->getId(),
        ]);
        $this->productResource->save($products[2]);
        //Reindexing
        $this->clearIndex();
        $this->indexer->reindexAll();
        //Checking that Category #1 now also shows Product #1 because it was
        //directly assigned to it and not showing Product #2 because it was
        //unassigned from Category #3.
        //Category #2 now shows Product #3 because it was directly assigned
        //to it.
        //Category #3 now shows only Product #3 because Product #2
        //was unassigned.
        //Category #4 still shows only Product #3.
        $this->assertProductIn($products[0], [$categories[0]], $categories);
        $this->assertProductIn($products[1], [], $categories);
        $this->assertProductIn($products[2], $categories, $categories);

        $this->clearIndex();
    }

    /**
     * Load categories from the fixture.
     *
     * @param int $limit
     * @param int $offset
     * @return Category[]
     */
    private function loadCategories(int $limit, int $offset = 0): array
    {
        /** @var Category $category */
        $category = Bootstrap::getObjectManager()->create(
            Category::class
        );

        $result = $category
            ->getCollection()
            ->addAttributeToSelect('name')
            ->getItems();
        $result = array_slice($result, 2);

        return array_slice($result, $offset, $limit);
    }

    /**
     * Load products from the fixture.
     *
     * @param int $limit
     * @param int $offset
     * @return Product[]
     */
    private function loadProducts(int $limit, int $offset = 0): array
    {
        /** @var Product[] $result */
        $result = [];
        $ids = range($offset + 1, $offset + $limit);
        foreach ($ids as $id) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = Bootstrap::getObjectManager()->create(
                Product::class
            );
            $result[] = $product->load($id);
        }

        return $result;
    }

    /**
     * Clear index data.
     */
    private function clearIndex()
    {
        $this->productResource->getConnection()->delete(
            $this->productResource->getTable('catalog_category_product_index')
        );
    }
}
