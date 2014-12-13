<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductAttributeAbsenceInUnassignedAttributes
 * Checks that product attribute isn't displayed in Product template's Unassigned Attributes section
 */
class AssertProductAttributeAbsenceInUnassignedAttributes extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that deleted attribute isn't displayed in Product template's Unassigned Attributes section
     *
     * @param CatalogAttributeSet $productTemplate
     * @param CatalogProductSetIndex $productSetIndex
     * @param CatalogProductSetEdit $productSetEdit
     * @return void
     */
    public function processAssert(
        CatalogAttributeSet $productTemplate,
        CatalogProductSetIndex $productSetIndex,
        CatalogProductSetEdit $productSetEdit
    ) {
        $filter = ['set_name' => $productTemplate->getAttributeSetName()];
        $productSetIndex->open();
        $productSetIndex->getGrid()->searchAndOpen($filter);

        $attributeCode = $productTemplate
            ->getDataFieldConfig('assigned_attributes')['source']
            ->getAttributes()[0]
            ->getAttributeCode();

        \PHPUnit_Framework_Assert::assertFalse(
            $productSetEdit->getAttributeSetEditBlock()->checkUnassignedProductAttribute($attributeCode),
            "Attribute " . $attributeCode . " is present in Unassigned Product template's section."
        );
    }

    /**
     * Text absent Product Attribute Unassigned Product template's section
     *
     * @return string
     */
    public function toString()
    {
        return "Product Attribute is absent in Unassigned Product template's section.";
    }
}
