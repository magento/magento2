<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint\Extension;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Block\Extension\AbstractGrid;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Check that there is extension on grid.
 */
class AssertFindExtensionOnGrid extends AbstractConstraint
{
    /**
     * Assert that there is extension on grid.
     *
     * @param AbstractGrid $grid
     * @param Extension $extension
     * @return void
     */
    public function processAssert(AbstractGrid $grid, Extension $extension)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $grid->findExtensionOnGrid($extension),
            'Extension is not found on the grid.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Extension is found on the grid.";
    }
}
