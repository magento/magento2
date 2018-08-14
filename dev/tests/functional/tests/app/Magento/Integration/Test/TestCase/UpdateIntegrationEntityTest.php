<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\TestCase;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Integration is created.
 *
 * Steps:
 * 1. Log in to backend as admin user.
 * 2. Navigate to System > Extensions > Integrations.
 * 3. Select an integration in the grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click "Save" button.
 * 6. Perform all assertions.
 *
 * @group Web_API_Framework
 * @ZephyrId MAGETWO-26102
 */
class UpdateIntegrationEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Integration grid page.
     *
     * @var IntegrationIndex
     */
    protected $integrationIndexPage;

    /**
     * Integration edit page.
     *
     * @var IntegrationNew
     */
    protected $integrationNewPage;

    /**
     * Injection data.
     *
     * @param IntegrationIndex $integrationIndex
     * @param IntegrationNew $integrationNew
     * @return void
     */
    public function __inject(IntegrationIndex $integrationIndex, IntegrationNew $integrationNew)
    {
        $this->integrationIndexPage = $integrationIndex;
        $this->integrationNewPage = $integrationNew;
    }

    /**
     * Update Integration Entity test.
     *
     * @param Integration $initialIntegration
     * @param Integration $integration
     * @return void
     */
    public function test(Integration $initialIntegration, Integration $integration)
    {
        // Precondition
        $initialIntegration->persist();

        // Steps
        $filter = ['name' => $initialIntegration->getName()];
        $this->integrationIndexPage->open();
        $this->integrationIndexPage->getIntegrationGrid()->searchAndOpen($filter);
        $this->integrationNewPage->getIntegrationForm()->fill($integration);
        $this->integrationNewPage->getFormPageActions()->save();
    }
}
