<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert no alert when saving an integration.
 */
class AssertNoAlertPopup extends AbstractConstraint
{
    /**
     * Assert no alert when saving an integration.
     *
     * @param IntegrationNew $integrationNew
     * @return void
     */
    public function processAssert(
        IntegrationNew $integrationNew
    ) {
        $isAlertPresent = $integrationNew->getFormPageActions()->isAlertPresent();
        if ($isAlertPresent) {
            $integrationNew->getFormPageActions()->acceptAlert();
        }
        \PHPUnit_Framework_Assert::assertFalse(
            $isAlertPresent,
            'Saving an integration should not cause alert.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Integration is saved with no alert.';
    }
}
