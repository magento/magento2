<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Constraint\Module;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;

/**
 * Class AssertGrid
 *
 * Checks whether Module is in grids.
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
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Module was found in grid.';
    }
}
