<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Check "Manufacturer's Suggested Retail Price" field on "Advanced pricing" page.
 */
class AssertProductEditPageAdvancedPricingFields extends AbstractConstraint
{
    /**
     * Title of "Manufacturer's Suggested Retail Price" field.
     *
     * @var string
     */
    private $manufacturerFieldTitle = 'Manufacturer\'s Suggested Retail Price';

    /**
     * @param CatalogProductEdit $catalogProductEdit
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CatalogProductEdit $catalogProductEdit, FixtureInterface $product)
    {
        $catalogProductEdit->open(['id' => $product->getId()]);
        $catalogProductEdit->getProductForm()->openSection('advanced-pricing');
        $advancedPricing = $catalogProductEdit->getProductForm()->getSection('advanced-pricing');

        \PHPUnit\Framework\Assert::assertTrue(
            $advancedPricing->checkField($this->manufacturerFieldTitle),
            '"Manufacturer\'s Suggested Retail Price" field is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return '"Manufacturer\'s Suggested Retail Price" field is correct.';
    }
}
