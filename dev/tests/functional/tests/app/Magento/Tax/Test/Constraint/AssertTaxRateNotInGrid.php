<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Tax\Test\Page\Adminhtml\TaxRateIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxRateNotInGrid
 */
class AssertTaxRateNotInGrid extends AbstractConstraint
{
    /**
     * Assert that tax rate not available in Tax Rate grid
     *
     * @param TaxRateIndex $taxRateIndex
     * @param TaxRate $taxRate
     * @return void
     */
    public function processAssert(
        TaxRateIndex $taxRateIndex,
        TaxRate $taxRate
    ) {
        $filter = [
            'code' => $taxRate->getCode(),
        ];

        $taxRateIndex->open();
        \PHPUnit\Framework\Assert::assertFalse(
            $taxRateIndex->getTaxRateGrid()->isRowVisible($filter),
            'Tax Rate \'' . $filter['code'] . '\' is present in Tax Rate grid.'
        );
    }

    /**
     * Text of Tax Rate not in grid assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax rate is absent in grid.';
    }
}
