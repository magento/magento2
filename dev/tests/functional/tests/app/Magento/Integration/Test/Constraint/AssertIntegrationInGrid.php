<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Constraint;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertIntegrationInGrid
 * Assert Integration availability in integration grid
 */
class AssertIntegrationInGrid extends AbstractConstraint
{
    /**
     * Assert that data in grid on Integrations page according to fixture by name field
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
        $filter = [
            'name' => ($initialIntegration !== null && !$integration->hasData('name'))
                ? $initialIntegration->getName()
                : $integration->getName(),
        ];

        $integrationIndexPage->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $integrationIndexPage->getIntegrationGrid()->isRowVisible($filter),
            'Integration \'' . $filter['name'] . '\' is absent in Integration grid.'
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Integration is present in grid.';
    }
}
