<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that there is no integration's been saved message.
 */
class AssertIntegrationSuccessSaveMessageNotPresent extends AbstractConstraint
{
    /**
     * Assert that there is no integration's been saved message.
     *
     * @param IntegrationIndex $integrationIndex
     * @return void
     */
    public function processAssert(
        IntegrationIndex $integrationIndex
    ) {
        $noSuccessMessage = false;
        if ($integrationIndex->getMessagesBlock()->isVisible()) {
            try {
                $integrationIndex->getMessagesBlock()->getSuccessMessage();
            } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                $noSuccessMessage = true;
            }
        } else {
            $noSuccessMessage = true;
        }
        \PHPUnit_Framework_Assert::assertTrue(
            $noSuccessMessage,
            'Integration is not saved.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Integration is not saved.';
    }
}
