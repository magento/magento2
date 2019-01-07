<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertIncorrectUserPassword
 * Assert that an error message is displayed in case current user password is incorrect.
 */
class AssertIncorrectUserPassword extends AbstractConstraint
{
    const ERROR_MESSAGE = "The password entered for the current user is invalid. Verify the password and try again.";

    /**
     * Assert that an error message is displayed on the Integration page in case current user password is incorrect.
     *
     * @param IntegrationIndex $integrationIndexPage
     * @return void
     */
    public function processAssert(
        IntegrationIndex $integrationIndexPage
    ) {
        $actualMessage = $integrationIndexPage->getMessagesBlock()->getErrorMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            self::ERROR_MESSAGE,
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::ERROR_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Incorrect password message is present and correct.';
    }
}
