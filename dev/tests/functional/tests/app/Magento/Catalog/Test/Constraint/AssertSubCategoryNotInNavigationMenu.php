<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check subCategory is not visible in navigation menu.
 */
class AssertSubCategoryNotInNavigationMenu extends AbstractConstraint
{
    /**
     * Assert subcategory is not visible in navigation menu.
     *
     * @param Category $subcategory
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(
        Category $subcategory,
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex
    ) {
        $cmsIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $catalogCategoryView->getTopmenu()->isCategoryVisible($subcategory->getName()),
            'Expected that ' . $subcategory->getName() . ' is not visible in navigation menu.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Subcategory is not visible in navigation menu";
    }
}
