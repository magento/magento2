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
 * Class AssertIntegrationSuccessSaveMessage
 * Assert that success save message is correct
 */
class AssertIntegrationSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_SAVE_MESSAGE = "The integration '%s' has been saved.";

    /**
     * Assert that success save message is appeared on the Integrations page
     *
     * @param IntegrationIndex $integrationIndexPage
     * @param Integration $integration
     * @param Integration|null $initialIntegration
     * @return void
     */
    public function processAssert(
        IntegrationIndex $integrationIndexPage,
        Integration $integration,
        Integration $initialIntegration = null
    ) {
        $name = ($initialIntegration !== null && !$integration->hasData('name'))
            ? $initialIntegration->getName()
            : $integration->getName();
        $expectedMessage = sprintf(self::SUCCESS_SAVE_MESSAGE, $name);
        $actualMessage = $integrationIndexPage->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedMessage,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . $expectedMessage
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
        return 'Integration success save message is correct.';
    }
}
