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
class AssertGridFullTextSearch extends AbstractConstraint
{
    /**
     * Assert that first item in grid is not the same on ascending and descending sorting
     *
     * @param array $results
     */
    public function processAssert(array $results)
    {
        foreach ($results as $itemId => $ids) {
            \PHPUnit_Framework_Assert::assertCount(
                1,
                $ids,
                sprintf(
                    'Full text search should find only %s item. Following items displayed: %s',
                    $itemId,
                    implode(', ', $ids)
                )
            );
            $actualItemId = $ids[0];
            \PHPUnit_Framework_Assert::assertEquals(
                $itemId,
                $actualItemId,
                sprintf(
                    '%d item is displayed instead of %d after full text search',
                    $actualItemId,
                    $itemId
                )
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
        return 'Full text search does not work as expected!';
    }
}
