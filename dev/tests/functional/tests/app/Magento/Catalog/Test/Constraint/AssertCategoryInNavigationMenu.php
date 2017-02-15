<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Check visibility of category in navigation menu.
 */
class AssertCategoryInNavigationMenu extends AbstractConstraint
{
    /**
     * Assert visibility of category in navigation menu.
     *
     * @param Category $category
     * @param CatalogCategoryView $catalogCategoryView
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        Category $category,
        CatalogCategoryView $catalogCategoryView,
        BrowserInterface $browser
    ) {
        $categoryData = $category->getData();

        $browser->open($_ENV['app_frontend_url']);
        if (($categoryData['include_in_menu'] == 'Yes') && ($categoryData['is_active'] == 'Yes')) {
            \PHPUnit_Framework_Assert::assertTrue(
                $catalogCategoryView->getTopmenu()->isCategoryVisible($category->getName()),
                'Category ' . $category->getName() . ' is not visible in navigation menu.'
            );
        } else {
            \PHPUnit_Framework_Assert::assertFalse(
                $catalogCategoryView->getTopmenu()->isCategoryVisible($category->getName()),
                'Category ' . $category->getName() . ' is visible in navigation menu.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Unexpected category's visibility in navigation menu";
    }
}
