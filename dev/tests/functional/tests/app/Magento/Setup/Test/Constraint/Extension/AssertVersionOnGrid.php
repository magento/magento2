<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint\Extension;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Block\Extension\Grid;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Check that version of extension is correct.
 */
class AssertVersionOnGrid extends AbstractConstraint
{
    /**#@+
     * Types of the job on extensions.
     */
    const TYPE_INSTALL = 1;
    const TYPE_UPDATE = 2;
    /*#@-*/

    /**
     * Assert that that version of extension is correct.
     *
     * @param Grid $grid
     * @param Extension $extension
     * @param int $type
     * @return void
     */
    public function processAssert(Grid $grid, Extension $extension, $type)
    {
        switch ($type) {
            case self::TYPE_INSTALL:
                $version = $extension->getVersion();
                break;

            case self::TYPE_UPDATE:
                $version = $extension->getVersionToUpdate();
                break;

            default:
                $version = '';
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $grid->getVersion($extension) === $version,
            'Version of extension is not correct.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Version of extension is correct.";
    }
}
