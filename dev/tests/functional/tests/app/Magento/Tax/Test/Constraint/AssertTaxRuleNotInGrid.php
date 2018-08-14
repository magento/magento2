<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxRuleNotInGrid
 */
class AssertTaxRuleNotInGrid extends AbstractConstraint
{
    /**
     * Assert that tax rule not available in Tax Rule grid
     *
     * @param TaxRuleIndex $taxRuleIndex
     * @param TaxRule $taxRule
     * @return void
     */
    public function processAssert(
        TaxRuleIndex $taxRuleIndex,
        TaxRule $taxRule
    ) {
        $filter = [
            'code' => $taxRule->getCode(),
        ];

        $taxRuleIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $taxRuleIndex->getTaxRuleGrid()->isRowVisible($filter),
            'Tax Rule \'' . $filter['code'] . '\' is present in Tax Rule grid.'
        );
    }

    /**
     * Text of Tax Rule not in grid assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax rule is absent in grid.';
    }
}
