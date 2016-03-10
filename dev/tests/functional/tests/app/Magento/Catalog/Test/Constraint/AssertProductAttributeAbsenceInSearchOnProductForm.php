<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Checks that product attribute cannot be added to attribute set on Product Page via Add Attribute control.
 */
class AssertProductAttributeAbsenceInSearchOnProductForm extends AbstractConstraint
{
    /**
     * Assert that deleted attribute can't be added to attribute set on Product Page via Add Attribute control.
     *
     * @param CatalogProductAttribute $productAttribute
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @param CatalogProductEdit $catalogProductEdit
     * @return void
     */
    public function processAssert(
        CatalogProductAttribute $productAttribute,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage,
        CatalogProductEdit $catalogProductEdit
    ) {
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');
        $catalogProductEdit->getFormPageActions()->addNewAttribute();
        $filter = [
            'label' => $productAttribute->getFrontendLabel(),
        ];
        \PHPUnit_Framework_Assert::assertFalse(
            $newProductPage->getProductForm()->checkIfAttributeExistsInGrid($filter),
            'Attribute \'' . $productAttribute->getFrontendLabel() . '\' is found in Attributes grid.'
        );
    }

    /**
     * Text absent Product Attribute in Attribute Search form.
     *
     * @return string
     */
    public function toString()
    {
        return "Product Attribute is absent in Attribute Search form.";
    }
}
