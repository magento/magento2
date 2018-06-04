<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that relations of categories in navigation menu are correct.
 */
class AssertCategoryNavigationMenu extends AbstractConstraint
{
    /**
     * Default category name.
     */
    const DEFAULT_CATEGORY_NAME = 'Default Category';

    /**
     * Assert that relations of categories in navigation menu are correct.
     *
     * @param CmsIndex $cmsIndex
     * @param Category $category
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        Category $category
    ) {
        do {
            $categoriesNames[] = $category->getName();
            $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
        } while ($category->getName() != self::DEFAULT_CATEGORY_NAME);

        $cmsIndex->open();

        foreach (array_reverse($categoriesNames) as $category) {
            \PHPUnit\Framework\Assert::assertTrue(
                $cmsIndex->getTopmenu()->isCategoryVisible($category),
                'Category ' . $category . ' is not visible in top menu.'
            );
            $cmsIndex->getTopMenu()->hoverCategoryByName($category);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Topmenu contains correct tree of categories.';
    }
}
