<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxRuleInGrid
 */
class AssertTaxRuleInGrid extends AbstractConstraint
{
    /**
     * Assert tax rule availability in Tax Rule grid
     *
     * @param TaxRuleIndex $taxRuleIndex
     * @param TaxRule $taxRule
     * @param TaxRule $initialTaxRule
     */
    public function processAssert(
        TaxRuleIndex $taxRuleIndex,
        TaxRule $taxRule,
        TaxRule $initialTaxRule = null
    ) {
        if ($initialTaxRule !== null) {
            $taxRuleCode = ($taxRule->hasData('code')) ? $taxRule->getCode() : $initialTaxRule->getCode();
        } else {
            $taxRuleCode = $taxRule->getCode();
        }
        $filter = [
            'code' => $taxRuleCode,
        ];

        $taxRuleIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $taxRuleIndex->getTaxRuleGrid()->isRowVisible($filter),
            'Tax Rule \'' . $filter['code'] . '\' is absent in Tax Rule grid.'
        );
    }

    /**
     * Text of Tax Rule in grid assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax rule is present in grid.';
    }
}
