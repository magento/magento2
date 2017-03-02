<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogRule\Test\Block\Adminhtml\Promo\Catalog\Edit\Section\RuleInformation;

/**
 * Assert that customer group is not on catalog price rule page.
 */
class AssertCustomerGroupNotOnCatalogPriceRuleForm extends AbstractConstraint
{
    /**
     * Assert that customer group is not on catalog price rule page.
     *
     * @param CatalogRuleNew $catalogRuleNew
     * @param CustomerGroup $customerGroup
     * @return void
     */
    public function processAssert(
        CatalogRuleNew $catalogRuleNew,
        CustomerGroup $customerGroup
    ) {
        $catalogRuleNew->open();
        $catalogRuleNew->getEditForm()->openSection('rule_information');

        /** @var RuleInformation $ruleInformationSection */
        $ruleInformationSection = $catalogRuleNew->getEditForm()->getSection('rule_information');
        \PHPUnit_Framework_Assert::assertFalse(
            $ruleInformationSection->isVisibleCustomerGroup($customerGroup),
            "Customer group {$customerGroup->getCustomerGroupCode()} is still in catalog price rule page."
        );
    }

    /**
     * Success assert of customer group absent on catalog price rule page.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group is not on catalog price rule page.';
    }
}
