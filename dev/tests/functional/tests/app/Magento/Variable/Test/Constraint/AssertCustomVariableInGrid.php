<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Constraint;

use Magento\Variable\Test\Fixture\SystemVariable;
use Magento\Variable\Test\Page\Adminhtml\SystemVariableIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that created custom variable is displayed on backend in custom variable grid and has correct data
 * according to dataset.
 */
class AssertCustomVariableInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert custom variable is displayed on backend in custom variable grid.
     *
     * @param SystemVariableIndex $systemVariableIndexNew
     * @param SystemVariable $customVariable
     * @return void
     */
    public function processAssert(
        SystemVariableIndex $systemVariableIndexNew,
        SystemVariable $customVariable
    ) {
        $filter = [
            'code' => $customVariable->getCode(),
            'name' => $customVariable->getName(),
        ];

        $systemVariableIndexNew->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $systemVariableIndexNew->getSystemVariableGrid()->isRowVisible($filter),
            'Custom Variable with code \'' . $filter['code'] . '\' is absent in Custom Variable grid.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Custom System Variable is present in grid.';
    }
}
