<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertTaxRuleSuccessDeleteMessage
 */
class AssertTaxRuleSuccessDeleteMessage extends AbstractConstraint
{
    const SUCCESS_DELETE_MESSAGE = 'The tax rule has been deleted.';

    /**
     * Assert that success delete message is displayed after tax rule deleted
     *
     * @param TaxRuleIndex $taxRuleIndex
     * @return void
     */
    public function processAssert(TaxRuleIndex $taxRuleIndex)
    {
        $actualMessage = $taxRuleIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $actualMessage,
            'Wrong success delete message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text of Deleted Tax Rule Success Message assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax rule success delete message is present.';
    }
}
