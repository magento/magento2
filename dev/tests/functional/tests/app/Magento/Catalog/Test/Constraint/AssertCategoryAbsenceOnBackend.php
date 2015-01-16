<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCategoryAbsenceOnBackend
 * Assert that not displayed category in backend catalog category tree
 */
class AssertCategoryAbsenceOnBackend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that not displayed category in backend catalog category tree
     *
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategory $category
     * @return void
     */
    public function processAssert(CatalogCategoryIndex $catalogCategoryIndex, CatalogCategory $category)
    {
        $catalogCategoryIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $catalogCategoryIndex->getTreeCategories()->isCategoryVisible($category),
            'Category is displayed in backend catalog category tree.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Category not displayed in backend catalog category tree.';
    }
}
