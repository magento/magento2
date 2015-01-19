<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Test\Constraint;

use Magento\Core\Test\Fixture\SystemVariable;
use Magento\Core\Test\Page\Adminhtml\SystemVariableIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomVariableNotInGrid
 */
class AssertCustomVariableNotInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert Custom System Variable not available in System Variable grid
     *
     * @param SystemVariableIndex $systemVariableIndexNew
     * @param SystemVariable $systemVariable
     * @return void
     */
    public function processAssert(
        SystemVariableIndex $systemVariableIndexNew,
        SystemVariable $systemVariable
    ) {
        $filter = [
            'code' => $systemVariable->getCode(),
            'name' => $systemVariable->getName(),
        ];

        $systemVariableIndexNew->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $systemVariableIndexNew->getSystemVariableGrid()->isRowVisible($filter),
            'Custom System Variable with code \'' . $filter['code'] . '\' is present in System Variable grid.'
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Custom System Variable is absent in grid.';
    }
}
