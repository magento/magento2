<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CurrencySymbol\Test\Page\Adminhtml\SystemCurrencyIndex;

/**
 * Assert that success message is displayed.
 */
class AssertCurrencyRateSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'All valid rates have been saved.';

    /**
     * Assert that success message is displayed after currency rate saved.
     *
     * @param SystemCurrencyIndex $currencyIndexPage
     * @return void
     */
    public function processAssert(SystemCurrencyIndex $currencyIndexPage)
    {
        $actualMessage = $currencyIndexPage->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Currency rate success create message is present.';
    }
}
