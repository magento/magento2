<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Catalog\Test\Fixture\Category;
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
     * @param Category $newCategory
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        Category $newCategory
    ) {
        do {
            $categoriesNames[] = $newCategory->getName();
            $newCategory = $newCategory->getDataFieldConfig('parent_id')['source']
                ->getParentCategory();
        } while ($newCategory->getName() != self::DEFAULT_CATEGORY_NAME);

        $cmsIndex->open();

        foreach (array_reverse($categoriesNames) as $category) {
            \PHPUnit_Framework_Assert::assertTrue(
                $cmsIndex->getTopMenu()->isCategoryVisible($category),
                'Category ' . $category . ' is not visible in top menu.'
            );
            $cmsIndex->getTopMenu()->hoverCategoryByName($category);
        }
    }

    /**
     * Assert success message that relations of categories in navigation menu are correct.
     *
     * @return string
     */
    public function toString()
    {
        return 'Topmenu contains correct tree of categories.';
    }
}
