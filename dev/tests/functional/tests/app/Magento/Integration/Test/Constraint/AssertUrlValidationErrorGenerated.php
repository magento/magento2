<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Magento\Integration\Test\Fixture\Integration;
use Magento\Mtf\Constraint\AbstractConstraint;
use PHPUnit\Framework\Assert;

/**
 * Assert validation error generated when saving integration with invalid callback url.
 */
class AssertUrlValidationErrorGenerated extends AbstractConstraint
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
        $urlJsError = false;
        foreach ($errors as $error) {
            if (strpos($error, 'Please enter a valid URL.') !== false) {
                $urlJsError = true;
                break;
            }
        }
        Assert::assertTrue(
            $urlJsError,
            'Failed to validate callback url (' . $integration->getEndpoint() . ') when saving integration.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Callback url is properly validated when saving integration.';
    }
}
