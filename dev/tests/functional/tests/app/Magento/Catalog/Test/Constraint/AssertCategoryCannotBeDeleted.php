<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that a category cannot be deleted.
 */
class AssertCategoryCannotBeDeleted extends AbstractConstraint
{
    /**
     * Assert that Delete button is not available.
     *
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @return void
     */
    public function processAssert(CatalogCategoryEdit $catalogCategoryEdit)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $catalogCategoryEdit->getFormPageActions()->checkDeleteButton(),
            false,
            'Delete button is available for the category.'
        );
    }

    /**
     * The category cannot be deleted.
     *
     * @return string
     */
    public function toString()
    {
        return 'The category cannot be deleted.';
    }
}
