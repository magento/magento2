<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Constraint;

use Magento\Variable\Test\Fixture\SystemVariable;
use Magento\Variable\Test\Page\Adminhtml\SystemVariableIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomVariableNotInGrid
 */
class AssertCustomVariableNotInGrid extends AbstractConstraint
{
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
        \PHPUnit\Framework\Assert::assertFalse(
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
