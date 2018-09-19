<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Asserts that tier price formatting correct
 */
class AssertProductFormattingTierPrice extends AbstractConstraint
{
    /**
     * Assert that success message is displayed after product save.
     *
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(CatalogProductEdit $productPage)
    {
        $productPage->getProductForm()->openSection('advanced-pricing');
        $productPage->getAdvancedPrice()->getFieldsData([]);
        $productPage->getAdvancedPrice()->getTierPriceForm()->waitTierPriceFormLocks();
        \PHPUnit\Framework\Assert::assertFalse(
            $productPage->getAdvancedPrice()->getTierPriceForm()->isVisible(),
            'Advanced price form still visible'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Advanced price formatting correct.';
    }
}
