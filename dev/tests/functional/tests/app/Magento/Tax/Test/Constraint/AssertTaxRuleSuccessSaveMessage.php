<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSuccessSavedMessageTaxRule
 */
class AssertTaxRuleSuccessSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_MESSAGE = 'The tax rule has been saved.';

    /**
     * Assert that success message is displayed after tax rule saved
     *
     * @param TaxRuleIndex $taxRuleIndex
     * @return void
     */
    public function processAssert(TaxRuleIndex $taxRuleIndex)
    {
        $actualMessage = $taxRuleIndex->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text of Created Tax Rule Success Message assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax rule success create message is present.';
    }
}
