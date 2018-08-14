<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that only matched items are shown on the grid after applying filter
 */
class AssertGridFiltering extends AbstractConstraint
{
    /**
     * Assert that first item in grid is not the same on ascending and descending sorting
     *
     * @param array $filterResults
     */
    public function processAssert(array $filterResults)
    {
        foreach ($filterResults as $itemId => $filters) {
            foreach ($filters as $filterName => $ids) {
                \PHPUnit_Framework_Assert::assertCount(
                    1,
                    $ids,
                    sprintf(
                        'Filtering by "%s" should result in only item id "%d" displayed. %s items ids present',
                        $itemId,
                        $filterName,
                        implode(', ', $ids)
                    )
                );
                $actualItemId = $ids[0];
                \PHPUnit_Framework_Assert::assertEquals(
                    $itemId,
                    $actualItemId,
                    sprintf(
                        '%d item is displayed instead of %d after applying "%s" filter',
                        $actualItemId,
                        $itemId,
                        $filterName
                    )
                );
            }
        }
    }

    /**
     * Return string representation of object
     *
     * @return string
     */
    public function toString()
    {
        return 'Filtering does not work as expected!';
    }
}
