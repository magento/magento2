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
     * Array for category tree.
     *
     * @var array
     */
    private $categoryTree = [];

    /**
     * Assert that relations of categories in navigation menu are correct.
     *
     * @param CmsIndex $cmsIndex
     * @param Category $bottomChildCategory
     * @param Category $childCategory
     * @param Category $parentCategory
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        Category $bottomChildCategory,
        Category $childCategory,
        Category $parentCategory
    ) {

        do {
            $name = $bottomChildCategory->getName();
            if ($name !== $childCategory->getName()) {
                $this->categoryTree[] = $name;
                $bottomChildCategory = $bottomChildCategory->getDataFieldConfig('parent_id')['source']
                    ->getParentCategory();
            } else {
                $this->categoryTree[] = $childCategory->getName();
                break;
            }
        } while ($name);

        if ($parentCategory->getName() !== 'Default Category') {
            $this->categoryTree[] = $parentCategory->getName();
        }
        $cmsIndex->open();

        foreach (array_reverse($this->categoryTree) as $category) {
            $cmsIndex->getTopMenu()->hoverCategoryByName($category);
            \PHPUnit_Framework_Assert::assertTrue(
                $cmsIndex->getTopMenu()->isCategoryVisible($category),
                'Category ' . $category . ' is not visible in top menu.'
            );
        }
    }

    /**
     * Assert success message that relations of categories in navigation menu are correct.
     *
     * @return string
     */
    public function toString()
    {
        return 'Topmenu contains correct tree of categories';
    }
}
