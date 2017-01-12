<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LayeredNavigation\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that layered navigation filter is preserved and products are sorted in the correct way.
 */
class AssertCategorySortingOnFilteredProductList extends AbstractConstraint
{
    /**
     * Available products list.
     *
     * @var array
     */
    private $products;

    /**
     * Assertion that products are filtered and sorted correctly.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param Category $category
     * @param array $layeredNavigation
     * @param array $sortBy
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        Category $category,
        array $layeredNavigation,
        array $sortBy
    ) {
        $this->products = $category->getDataFieldConfig('category_products')['source']->getProducts();
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($category->getName());
        $filteredIndexes = range(0, count($this->products));
        foreach ($layeredNavigation as $filters) {
            foreach ($filters as $filter) {
                $catalogCategoryView->getLayeredNavigationBlock()->applyFilter(
                    $filter['title'],
                    $filter['linkPattern']
                );
                $filteredIndexes = array_intersect(
                    $filteredIndexes,
                    array_map(
                        function ($productKey) {
                            return str_replace('product_', '', trim($productKey));
                        },
                        explode(',', $filter['products'])
                    )
                );
            }
        }
        $catalogCategoryView->getTopToolbar()->applySorting($sortBy);
        \PHPUnit_Framework_Assert::assertEquals(
            array_map(
                function ($index) {
                    return $this->products[$index]->getName();
                },
                array_values($filteredIndexes)
            ),
            $catalogCategoryView->getListProductBlock()->getProductNames(),
            'Products are filtered or sorted incorrectly.'
        );
        $catalogCategoryView->getLayeredNavigationBlock()->clearAll();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products are filtered and sorted in the correct way.';
    }
}
