<?php

namespace Magento\Config\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Config\Test\Page\Adminhtml\AdminAccountSharing;
use Magento\Config\Test\Fixture\ConfigDataWithAdminAccountSharing;

/**
 * Steps:
 * 1. Log in to Admin.
 * 2. Open the Email Templates page.
 * 3. Click the "Add New Template" button.
 * 4. Select Email Template.
 * 5. Click the "Load Template" button.
 * 6. Enter Email Template name.
 * 7. Verify the email template saved successfully.
 *
 * @group Email_(PS)
 * @ZephyrId MAGETWO-17155
 */
class VerifyAdminAccountSharingEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Email Template Index page.
     *
     * @var AdminAccountSharing
     */
    private $adminAccountSharing;

    /**
     * Inject synonym pages.
     *
     * @param $AdminAccountSharing $AdminAccountSharing
     * @return void
     */
    public function __inject(
        AdminAccountSharing $AdminAccountSharing
    ) {
        $this->adminAccountSharing = $AdminAccountSharing;
    }

    /**
     * Create Verify Admin Account Sharing test.
     *
     * @param ConfigDataWithAdminAccountSharing $ConfigDataWithAdminAccountSharing
     * @return void
     */
    public function test(ConfigDataWithAdminAccountSharing $ConfigDataWithAdminAccountSharing)
    {
        $this->adminAccountSharing->open();

    }
}