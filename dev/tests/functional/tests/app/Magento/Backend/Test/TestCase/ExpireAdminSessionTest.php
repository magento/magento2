<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;
use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 *
 * 1. Login to backend.
 * 2. Go to Stores -> Configuration -> Advanced -> Admin.
 * 3. Open "Security" group.
 * 4. Save old session lifetime value for "Admin Session Lifetime (seconds)".
 * 5. Set new session lifetime value for "Admin Session Lifetime (seconds)".
 * 6. Wait for session to expire.
 * 7. Go to Stores -> Configuration.
 * 8. Perform asserts.
 * 9. Restore old session lifetime value after test is done.
 *
 * @ZephyrId MAGETWO-47723
 */
class ExpireAdminSessionTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

    protected $systemConfigEdit;

    protected $sessionLifetimeConfig;

    protected $oldValue;

    /**
     * Open backend system config.
     * Save default configuration value.
     * Set new configuration value.
     * Reopen backend system config.
     *
     * @param SystemConfigEdit $systemConfigEdit
     * @param ConfigData $sessionLifetimeConfig
     * @param AdminAuthLogin $adminAuthLogin
     * @return void
     */
    public function test(
        SystemConfigEdit $systemConfigEdit,
        ConfigData $sessionLifetimeConfig,
        AdminAuthLogin $adminAuthLogin
    ) {
        $this->systemConfigEdit = $systemConfigEdit;
        $this->sessionLifetimeConfig = $sessionLifetimeConfig;
        $this->systemConfigEdit->open();
        $section = $sessionLifetimeConfig->getSection();
        $keys = array_keys($section);
        $parts = explode('/', $keys[0], 3);
        $tabName = $parts[0];
        $groupName = $parts[1];
        $fieldName = $parts[2];
        $this->oldValue = $this->systemConfigEdit->getForm()->getGroup($tabName, $groupName)
            ->getValue($tabName, $groupName, $fieldName);
        $this->systemConfigEdit->getForm()->getGroup($tabName, $groupName)
            ->setValue($tabName, $groupName, $fieldName, $section[$keys[0]]['label']);
        $this->systemConfigEdit->getPageActions()->save();
        $this->systemConfigEdit->getMessagesBlock()->waitSuccessMessage();

        /**
         * Wait admin session to expire.
         */
        sleep($section[$keys[0]]['label']);

        $adminAuthLogin->open();
    }

    /**
     * Tear down after tests.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->systemConfigEdit->open();
        $section = $this->sessionLifetimeConfig->getSection();
        $keys = array_keys($section);
        $parts = explode('/', $keys[0], 3);
        $tabName = $parts[0];
        $groupName = $parts[1];
        $fieldName = $parts[2];
        $this->systemConfigEdit->getForm()->getGroup($tabName, $groupName)
            ->setValue($tabName, $groupName, $fieldName, $this->oldValue);
        $this->systemConfigEdit->getPageActions()->save();
        $this->systemConfigEdit->getMessagesBlock()->waitSuccessMessage();
    }
}
