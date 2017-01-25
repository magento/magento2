<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;

/**
 * Verify visibility of Locale field and Developer section on Configuration page.
 *
 * @ZephyrId MAGETWO-63625, MAGETWO-63624
 */
class ConfigPageVisibilityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * "Configuration" page in Admin panel.
     *
     * @var SystemConfigEdit
     */
    protected $configurationAdminPage;

    /**
     * Prepare data for further test execution.
     *
     * @param SystemConfigEdit $configurationAdminPage
     * @return void
     */
    public function __inject(SystemConfigEdit $configurationAdminPage)
    {
        $this->configurationAdminPage = $configurationAdminPage;
    }

    /**
     * Test execution.
     *
     * @return void
     */
    public function test()
    {
        $this->configurationAdminPage->open();
    }
}
