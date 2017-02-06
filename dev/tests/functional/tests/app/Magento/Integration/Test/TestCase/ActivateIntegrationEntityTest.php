<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\TestCase;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Integration is created.
 *
 * Steps:
 * 1. Log in to backend as admin user.
 * 2. Navigate to System > Extensions > Integrations.
 * 3. Click on the "Activate" link near required integration.
 * 4. Perform all assertions.
 *
 * @group Web_API_Framework
 * @ZephyrId MAGETWO-26119
 */
class ActivateIntegrationEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Integration grid page.
     *
     * @var IntegrationIndex
     */
    protected $integrationIndexPage;

    /**
     * Injection data.
     *
     * @param IntegrationIndex $integrationIndex
     * @return void
     */
    public function __inject(IntegrationIndex $integrationIndex)
    {
        $this->integrationIndexPage = $integrationIndex;
    }

    /**
     * Activate Integration Entity Test.
     *
     * @param Integration $integration
     * @return void
     */
    public function test(Integration $integration)
    {
        // Preconditions
        $integration->persist();

        // Steps
        $filter = ['name' => $integration->getName()];
        $this->integrationIndexPage->open();
        $this->integrationIndexPage->getIntegrationGrid()->searchAndActivate($filter);
    }
}
