<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertGridSorting
 * Assert that first item in grid is not the same on ascending and descending sorting
 */
class AssertGridSorting extends AbstractConstraint
{
    /**
     * Assert that first item in grid is not the same on ascending and descending sorting
     *
     * @param array $sortingResults
     */
    public function processAssert(array $sortingResults)
    {
        foreach ($sortingResults as $columnName => $sortingResult) {
            \PHPUnit\Framework\Assert::assertNotEquals(
                $sortingResult['firstIdAfterFirstSoring'],
                $sortingResult['firstIdAfterSecondSoring'],
                sprintf('Sorting for "%s" column have not changed the first item of grid!', $columnName)
            );
        }
    }

    /**
     * Return string representation of object
     *
     * @return string
     */
    public function toString()
    {
        return 'Sorting have not changed the first item of grid!';
    }
}
