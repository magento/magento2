<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAttributeSetForm
 * Checking data from Attribute Set form with data fixture
 */
class AssertAttributeSetForm extends AbstractConstraint
{
    /**
     * Assert that after save a attribute set on edit product set page displays:
     * 1. Correct attribute set name in Attribute set name field passed from fixture
     * 2. Created Product Attribute (if was added)
     *
     * @param CatalogProductSetIndex $productSet
     * @param CatalogProductSetEdit $productSetEdit
     * @param CatalogAttributeSet $attributeSet
     * @param CatalogProductAttribute $productAttribute
     * @return void
     */
    public function processAssert(
        CatalogProductSetIndex $productSet,
        CatalogProductSetEdit $productSetEdit,
        CatalogAttributeSet $attributeSet,
        CatalogProductAttribute $productAttribute = null
    ) {
        $filterAttribute = [
            'set_name' => $attributeSet->getAttributeSetName(),
        ];
        $productSet->open();
        $productSet->getGrid()->searchAndOpen($filterAttribute);
        \PHPUnit_Framework_Assert::assertEquals(
            $filterAttribute['set_name'],
            $productSetEdit->getAttributeSetEditBlock()->getAttributeSetName(),
            'Attribute Set not found'
            . "\nExpected: " . $filterAttribute['set_name']
            . "\nActual: " . $productSetEdit->getAttributeSetEditBlock()->getAttributeSetName()
        );
        if ($productAttribute !== null) {
            $attributeLabel = $productAttribute->getFrontendLabel();
            \PHPUnit_Framework_Assert::assertTrue(
                $productSetEdit->getAttributeSetEditBlock()->checkProductAttribute($attributeLabel),
                "Product Attribute is absent on Attribute Set Groups"
            );
        }
    }

    /**
     * Text matches the data from a form with data from fixture
     *
     * @return string
     */
    public function toString()
    {
        return 'Data from the Attribute Set form matched with fixture';
    }
}
