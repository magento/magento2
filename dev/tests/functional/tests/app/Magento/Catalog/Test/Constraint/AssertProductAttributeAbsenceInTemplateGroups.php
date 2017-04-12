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
 * Class AssertProductAttributeAbsenceInTemplateGroups
 * Checks that product attribute isn't displayed in Attribute set's Groups section
 */
class AssertProductAttributeAbsenceInTemplateGroups extends AbstractConstraint
{
    /**
     * Assert that deleted attribute isn't displayed in Attribute set's Groups section
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
            $productSetEdit->getAttributeSetEditBlock()->checkProductAttribute($attributeCode),
            "Attribute " . $attributeCode . " is present in Attribute set's Groups section."
        );
    }

    /**
     * Text absent Product Attribute in Attribute set's Groups section
     *
     * @return string
     */
    public function toString()
    {
        return "Product Attribute is absent in Attribute set's Groups section.";
    }
}
