<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\TestCase;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Log in to backend as admin user.
 * 2. Navigate to System > Extensions > Integrations.
 * 3. Start to create new Integration.
 * 4. Fill in all data according to data set.
 * 5. Click "Save" button.
 * 6. Perform all assertions.
 *
 * @group Web_API_Framework_(PS)
 * @ZephyrId MAGETWO-26009, MAGETWO-16755, MAGETWO-16819, MAGETWO-16820
 */
class CreateIntegrationEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Integration grid page.
     *
     * @var IntegrationIndex
     */
    protected $integrationIndexPage;

    /**
     * Integration new page.
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
     * Create Integration Entity test.
     *
     * @param Integration $integration
     * @return void
     */
    public function test(Integration $integration)
    {
        // Steps
        $this->integrationIndexPage->open();
        $this->integrationIndexPage->getGridPageActions()->addNew();
        $this->integrationNewPage->getIntegrationForm()->fill($integration);
        $this->integrationNewPage->getFormPageActions()->saveNew();
    }
}
