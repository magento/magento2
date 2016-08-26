<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\TestCase;

use Magento\Integration\Test\Fixture\Integration;
use Magento\Integration\Test\Page\Adminhtml\IntegrationIndex;
use Magento\Integration\Test\Page\Adminhtml\IntegrationNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Log in to backend as admin user
 * 2. Navigate to System > Extensions > Integrations
 * 3. Click 'Add New Integration'
 * 4. Fill in all required data
 * 5. Click "Save" button to save Integration1
 * 6. Click 'Add New Integration'
 * 7. Fill in all required data and use the same name as for Integration1
 * 8. Click "Save" button
 * 9. Perform all assertions
 *
 * @group Web_API_Framework
 * @ZephyrId MAGETWO-16756
 */
class CreateIntegrationWithDuplicatedNameTest extends Injectable
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
    public function __inject(
        IntegrationIndex $integrationIndex,
        IntegrationNew $integrationNew
    ) {
        $this->integrationIndexPage = $integrationIndex;
        $this->integrationNewPage = $integrationNew;
    }

    /**
     * Create Integration Entity with existing name test.
     *
     * @param Integration $integration
     * @return array
     */
    public function test(Integration $integration)
    {
        // Precondition
        $integration->persist();

        // Steps
        $this->integrationIndexPage->open();
        $this->integrationIndexPage->getGridPageActions()->addNew();
        $this->integrationNewPage->getIntegrationForm()->fill($integration);
        $this->integrationNewPage->getFormPageActions()->saveNew();
        return ['integration' => $integration];
    }
}
