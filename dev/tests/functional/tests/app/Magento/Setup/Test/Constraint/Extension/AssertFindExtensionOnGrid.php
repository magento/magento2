<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint\Extension;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Block\Extension\AbstractGrid;

/**
 * Check that there is extension on grid
 */
class AssertFindExtensionOnGrid extends AbstractConstraint
{
    /**
     * Assert upgrade is successfully
     *
     * @param AbstractGrid $grid
     * @param string $name
     * @return void
     */
    public function processAssert(AbstractGrid $grid, $name)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $grid->isExtensionOnGrid($name),
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
