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
 * Assert that pop-up with resources are shown after starting activation.
 */
class AssertIntegrationResourcesPopup extends AbstractConstraint
{
    /**
     * Assert that pop-up with resources, that were specified for integration are shown
     * after starting activation of integration.
     *
     * @param IntegrationIndex $integrationIndex
     * @param Integration $integration
     * @param int|null $resourceDepth
     * @return void
     */
    public function processAssert(IntegrationIndex $integrationIndex, Integration $integration, $resourceDepth = null)
    {
        $fixtureResources = is_array($integration->getResources())
            ? $integration->getResources()
            : [$integration->getResources()];
        $formResources = $integrationIndex->getIntegrationGrid()->getResourcesPopup()->getStructure($resourceDepth);
        $result = $this->verifyResources($formResources, $fixtureResources);
        \PHPUnit\Framework\Assert::assertEmpty(
            $result,
            "Integration resources is not correct.\nLog:\n" . $result
        );
        $integrationIndex->getIntegrationGrid()->getResourcesPopup()->clickAllowButton();
    }

    /**
     * Verify that resources are correct.
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
                implode(",\n", $formResources),
                implode(",\n", $topFormResources)
            );
        }

        return $errorMessage;
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Resources in popup window are shown correctly.';
    }
}
