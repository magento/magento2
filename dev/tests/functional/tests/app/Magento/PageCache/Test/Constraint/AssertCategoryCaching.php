<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Constraint\AssertProductNotVisibleInCategory;
use Magento\Catalog\Test\Constraint\AssertProductInCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Util\Command\Cli\Cron;

/**
 * Assert that category products are visible after indexing.
 */
class AssertCategoryCaching extends AbstractConstraint
{
    /**
     * Assert category products are displayed only after indexing.
     *
     * @param Category $category
     * @param CatalogCategoryView $categoryView
     * @param CmsIndex $cmsIndex
     * @param AssertProductInCategory $assertProduct
     * @param AssertProductNotVisibleInCategory $assertProductNotVisible
     * @param Cron $cron
     * @return void
     */
    public function processAssert(
        Category $category,
        CatalogCategoryView $categoryView,
        CmsIndex $cmsIndex,
        AssertProductInCategory $assertProduct,
        AssertProductNotVisibleInCategory $assertProductNotVisible,
        Cron $cron
    ) {
        $products = $category->getDataFieldConfig('category_products')['source']->getProducts();
        foreach ($products as $product) {
            $assertProductNotVisible->processAssert($categoryView, $cmsIndex, $product, $category);
        }

        $cron->run();
        $cron->run();

        foreach ($products as $product) {
            $assertProduct->processAssert($categoryView, $cmsIndex, $product, $category);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category products are visible after indexing.';
    }
}
