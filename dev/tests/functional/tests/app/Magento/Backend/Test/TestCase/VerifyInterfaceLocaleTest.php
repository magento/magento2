<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\TestCase;

use Magento\Backend\Test\Constraint\AssertInterfaceLocaleAvailableOptions;
use Magento\Backend\Test\Page\Adminhtml\SystemAccount;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Util\Command\Locales;
use Magento\User\Test\Page\Adminhtml\UserEdit;

/**
 * Verify that Interface Locales has correct options on Account Settings and User Edit pages.
 *
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate to "Account Setting" page.
 * 3. Perform interface locales asserts depends on magento mode.
 * 4. Navigate to "User Edit Page".
 * 5. Perform interface locales asserts depends on magento mode.
 *
 * @ZephyrId MAGETWO-64920, MAGETWO-64921
 */
class VerifyInterfaceLocaleTest extends Injectable
{
    /* tags */
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * "Account settings" page in Admin panel.
     *
     * @var SystemAccount
     */
    private $systemAccountPage;

    /**
     * "User Edit" page in Admin panel.
     *
     * @var UserEdit
     */
    private $userEditPage;

    /**
     * Prepare data for further test execution.
     *
     * @param SystemAccount $systemAccountPage
     * @param UserEdit $userEdit
     */
    public function __inject(
        SystemAccount $systemAccountPage,
        UserEdit $userEdit
    ) {
        $this->systemAccountPage = $systemAccountPage;
        $this->userEditPage = $userEdit;
    }

    /**
     * Test execution.
     *
     * @param AssertInterfaceLocaleAvailableOptions $assertInterfaceLocaleAvailableOptions assert that check
     *        interface locales
     * @param Locales $locales utility for work with locales
     */
    public function test(
        AssertInterfaceLocaleAvailableOptions $assertInterfaceLocaleAvailableOptions,
        Locales $locales
    ) {
        $this->systemAccountPage->open();
        $userForm = $this->systemAccountPage->getForm();
        $assertInterfaceLocaleAvailableOptions->processAssert(
            $locales,
            $userForm->getInterfaceLocales()
        );

        $this->userEditPage->open();
        $userForm = $this->userEditPage->getUserForm();
        $assertInterfaceLocaleAvailableOptions->processAssert(
            $locales,
            $userForm->getInterfaceLocales()
        );
    }
}
