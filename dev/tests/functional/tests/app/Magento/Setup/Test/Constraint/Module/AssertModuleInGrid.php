<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Constraint\Module;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;

/**
 * Class AssertGrid
 *
 * Checks whether Module presents in the grid.
 */
class AssertModuleInGrid extends AbstractConstraint
{
    /**
     * Recursively search for the Module name.
     *
     * @param SetupWizard $setupWizard
     * @param string $moduleName
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard, $moduleName)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $setupWizard->getModuleGrid()->findModuleByName($moduleName)->isVisible(),
            'Module was not found in grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Module was found in grid.';
    }
}
