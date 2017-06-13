<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Constraint;

use Magento\CurrencySymbol\Test\Page\Adminhtml\SystemCurrencySymbolIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCurrencySymbolSuccessSaveMessage
 * Check that after clicking on 'Save Currency Symbols' button success message appears.
 */
class AssertCurrencySymbolSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_SAVE_MESSAGE = 'You applied the custom currency symbols.';

    /**
     * Assert that after clicking on 'Save Currency Symbols' button success message appears.
     *
     * @param SystemCurrencySymbolIndex $currencySymbolIndex
     * @return void
     */
    public function processAssert(SystemCurrencySymbolIndex $currencySymbolIndex)
    {
        $actualMessage = $currencySymbolIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_SAVE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Currency Symbol success save message is correct.';
    }
}
