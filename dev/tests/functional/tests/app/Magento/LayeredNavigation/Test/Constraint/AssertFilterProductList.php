<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LayeredNavigation\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check whether products can be filtered in the Frontend via layered navigation.
 */
class AssertFilterProductList extends AbstractConstraint
{
    /**
     * Available products list.
     *
     * @var array
     */
    protected $products;

    /**
     * Assertion that filtered product list via layered navigation are displayed correctly.
     *
     * @param Category $category
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param array $layeredNavigation
     * @return void
     */
    public function processAssert(
        Category $category,
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        array $layeredNavigation
    ) {
        $this->products = $category->getDataFieldConfig('category_products')['source']->getProducts();
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($category->getName());

        foreach ($layeredNavigation as $filters) {
            foreach ($filters as $filter) {
                $catalogCategoryView->getLayeredNavigationBlock()->applyFilter(
                    $filter['title'],
                    $filter['linkPattern']
                );

                $productNames = $this->getProductNames($filter['products']);
                sort($productNames);
                $pageProductNames = $catalogCategoryView->getListProductBlock()->getProductNames();
                sort($pageProductNames);
                \PHPUnit_Framework_Assert::assertEquals($productNames, $pageProductNames);
            }
            $catalogCategoryView->getLayeredNavigationBlock()->clearAll();
        }
    }

    /**
     * Get product names list by keys.
     *
     * @param string $productKeys
     * @return array
     */
    protected function getProductNames($productKeys)
    {
        $keys = array_map('trim', explode(',', $productKeys));
        $productNames = [];

        foreach ($keys as $key) {
            $key = str_replace('product_', '', $key);
            $productNames[] = $this->products[$key]->getName();
        }

        return $productNames;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Filtered product list via layered navigation are displayed correctly.';
    }
}
