<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAbsenceDeleteAttributeButton
 * Checks the button "Delete Attribute" on the Attribute page
 */
class AssertAbsenceDeleteAttributeButton extends AbstractConstraint
{
    /**
     * Assert that Delete Attribute button is absent for system attribute on attribute edit page.
     *
     * @param CatalogProductAttributeNew $attributeNew
     * @return void
     */
    public function processAssert(CatalogProductAttributeNew $attributeNew)
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $attributeNew->getPageActions()->checkDeleteButton(),
            "Button 'Delete Attribute' is present on Attribute page"
        );
    }

    /**
     * Text absent button "Delete Attribute" on the Attribute page
     *
     * @return string
     */
    public function toString()
    {
        return "Button 'Delete Attribute' is absent on Attribute Page.";
    }
}
