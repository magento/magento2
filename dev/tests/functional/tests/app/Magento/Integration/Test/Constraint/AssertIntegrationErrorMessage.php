<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert error message is displayed in message block.
 */
class AssertIntegrationErrorMessage extends AbstractConstraint
{
    /**
     * Assert error message is displayed in message block.
     *
     * @param IntegrationIndex $integrationIndexPage
     * @param Integration $integration
     * @param string $errorMessage
     * @return void
     */
    public function processAssert(
        IntegrationIndex $integrationIndexPage,
        Integration $integration,
        $errorMessage
    ) {
        $expectedMessage = sprintf($errorMessage, $integration->getName());
        $actualMessage = $integrationIndexPage->getMessagesBlock()->getErrorMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedMessage,
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . $expectedMessage
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
        return 'Integration error message is correct.';
    }
}
