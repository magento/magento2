<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\AdvancedPricing;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that customer group find on product page.
 */
class AssertCustomerGroupOnProductForm extends AbstractConstraint
{
    /**
     * Assert that customer group find on product page.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductNew $catalogProductNew
     * @param CustomerGroup $customerGroup
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductNew $catalogProductNew,
        CustomerGroup $customerGroup
    ) {
        $catalogProductIndex->open();
        $catalogProductIndex->getGridPageActionBlock()->addProduct();
        $catalogProductNew->getProductForm()->openSection('advanced-pricing');

        /** @var AdvancedPricing $advancedPricingTab */
        $advancedPricingTab = $catalogProductNew->getProductForm()->getSection('advanced-pricing');
        \PHPUnit\Framework\Assert::assertTrue(
            $advancedPricingTab->getTierPriceForm()->isVisibleCustomerGroup($customerGroup),
            "Customer group {$customerGroup->getCustomerGroupCode()} not in tier price form on product page."
        );
    }

    /**
     * Success assert of customer group find on product page.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group find on product page.';
    }
}
