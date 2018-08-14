<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductAttributeAbsenceInUnassignedAttributes
 * Checks that product attribute isn't displayed in Attribute set's Unassigned Attributes section
 */
class AssertProductAttributeAbsenceInUnassignedAttributes extends AbstractConstraint
{
    /**
     * Assert that deleted attribute isn't displayed in Attribute set's Unassigned Attributes section
     *
     * @param CatalogAttributeSet $attributeSet
     * @param CatalogProductSetIndex $productSetIndex
     * @param CatalogProductSetEdit $productSetEdit
     * @return void
     */
    public function processAssert(
        CatalogAttributeSet $attributeSet,
        CatalogProductSetIndex $productSetIndex,
        CatalogProductSetEdit $productSetEdit
    ) {
        $filter = ['set_name' => $attributeSet->getAttributeSetName()];
        $productSetIndex->open();
        $productSetIndex->getGrid()->searchAndOpen($filter);

        $attributeCode = $attributeSet
            ->getDataFieldConfig('assigned_attributes')['source']
            ->getAttributes()[0]
            ->getAttributeCode();

        \PHPUnit_Framework_Assert::assertFalse(
            $productSetEdit->getAttributeSetEditBlock()->checkUnassignedProductAttribute($attributeCode),
            "Attribute " . $attributeCode . " is present in Unassigned Attribute set's section."
        );
    }

    /**
     * Text absent Product Attribute Unassigned Attribute set's section
     *
     * @return string
     */
    public function toString()
    {
        return "Product Attribute is absent in Unassigned Attribute set's section.";
    }
}
