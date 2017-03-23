<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint\Extension;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Block\Extension\AbstractGrid;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Check that several extensions were selected on the grid.
 */
class AssertSelectSeveralExtensions extends AbstractConstraint
{
    /**
     * Assert that extensions were selected on the grid.
     *
     * @param AbstractGrid $grid
     * @param Extension[] $extensions
     * @return void
     */
    public function processAssert(AbstractGrid $grid, array $extensions)
    {
        $extensions = $grid->selectSeveralExtensions($extensions);
        \PHPUnit_Framework_Assert::assertEmpty(
            $extensions,
            'Next extensions are not found on the grid: ' . $this->getExtensionsNames($extensions)
        );
    }

    /**
     * Get names of extensions.
     *
     * @param Extension[] $extensions
     * @return string
     */
    protected function getExtensionsNames(array $extensions)
    {
        $result = [];
        foreach ($extensions as $extension) {
            $result[] = $extension->getExtensionName();
        }

        return implode(', ', $result);
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Extensions are found and selected on the grid.";
    }
}
