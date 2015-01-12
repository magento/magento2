<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertIntegrationResourcesPopup
 * Assert that pop-up with resources are shown after starting activation
 */
class AssertIntegrationResourcesPopup extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that pop-up with resources, that were specified for integration are shown
     * after starting activation of integration
     *
     * @param IntegrationIndex $integrationIndex
     * @param Integration $integration
     * @return void
     */
    public function processAssert(IntegrationIndex $integrationIndex, Integration $integration)
    {
        $fixtureResources = is_array($integration->getResources())
            ? $integration->getResources()
            : [$integration->getResources()];
        $formResources = $integrationIndex->getIntegrationGrid()->getResourcesPopup()->getData();
        $result = $this->verifyResources($formResources['resources'], $fixtureResources);
        \PHPUnit_Framework_Assert::assertEmpty(
            $result,
            "Integration resources is not correct.\nLog:\n" . $result
        );
        $integrationIndex->getIntegrationGrid()->getResourcesPopup()->clickAllowButton();
    }

    /**
     * Verify that resources are correct
     *
     * @param array $formResources
     * @param array $fixtureResources
     * @return string
     */
    protected function verifyResources(array $formResources, array $fixtureResources)
    {
        $errorMessage = '';
        $topFormResources = [];

        foreach ($fixtureResources as $fixtureResource) {
            foreach ($formResources as $formResource) {
                if (preg_match('|^' . preg_quote($fixtureResource) . '|', $formResource)) {
                    $topFormResources[] = $formResource;
                }
            }
        }
        $diff = array_diff($formResources, $topFormResources);
        if (!empty($diff)) {
            $errorMessage = sprintf(
                "Resources are not equal.\nExpected: %s\nActual: %s",
                implode(",\n", $fixtureResources),
                implode(",\n", $formResources)
            );
        }

        return $errorMessage;
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Resources in popup window are shown correctly.';
    }
}
