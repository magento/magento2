<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Magento\Integration\Test\Fixture\Integration;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert validation error generated when saving integration with invalid email.
 */
class AssertEmailValidationErrorGenerated extends AbstractConstraint
{
    /**
     * Assert validation error generated when saving integration with invalid email.
     *
     * @param IntegrationNew $integrationNew
     * @param Integration $integration
     * @return void
     */
    public function processAssert(
        IntegrationNew $integrationNew,
        Integration $integration
    ) {
        $errors = $integrationNew->getIntegrationForm()->getJsErrors("integration_info");
        $emailJsError = false;
        foreach ($errors as $error) {
            if (strpos($error, 'Please enter a valid email address') !== false) {
                $emailJsError = true;
                break;
            }
        }
        \PHPUnit_Framework_Assert::assertTrue(
            $emailJsError,
            'Failed to validate email address (' . $integration->getEmail() . ') when saving integration.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Email address is properly validated when saving integration.';
    }
}
