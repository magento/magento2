<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert error message is displayed in message block.
 */
class AssertIntegrationNameDuplicationErrorMessage extends AbstractConstraint
{
    const ERROR_DUPLICATE_INTEGRATION_NAME = "Integration with name '%s' exists.";

    /**
     * Assert error message is displayed in message block.
     *
     * @param IntegrationIndex $integrationIndexPage
     * @param Integration $integration
     * @return void
     */
    public function processAssert(
        IntegrationIndex $integrationIndexPage,
        Integration $integration
    ) {
        $expectedMessage = sprintf(self::ERROR_DUPLICATE_INTEGRATION_NAME, $integration->getName());
        $actualMessage = $integrationIndexPage->getMessagesBlock()->getErrorMessage();
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
        return 'Duplicated integration name error message is correct.';
    }
}
