<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Handler\Ui;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Ui;

/**
 * Class LoginUser
 * Handler for ui backend user login
 *
 */
class LoginUser extends Ui
{
    /**
     * Login admin user
     *
     * @param FixtureInterface $fixture [optional]
     * @return void|mixed
     */
    public function persist(FixtureInterface $fixture = null)
    {
        if (null === $fixture) {
            $fixture = Factory::getFixtureFactory()->getMagentoBackendAdminSuperAdmin();
        }

        /** @var AdminAuthLogin $loginPage */
        $loginPage = Factory::getPageFactory()->getAdminAuthLogin();
        $loginForm = $loginPage->getLoginBlock();
        $adminHeaderPanel = $loginPage->getHeaderBlock();
        if (!$loginForm->isVisible() && !$adminHeaderPanel->isVisible()) {
            //We are currently not in the admin area.
            $loginPage->open();
        }

        if (!$adminHeaderPanel || !$adminHeaderPanel->isVisible()) {
            $loginPage->open();
            if ($adminHeaderPanel->isVisible()) {
                return;
            }
            $loginForm->fill($fixture);
            $loginForm->submit();
            $loginPage->waitForHeaderBlock();
            $loginPage->dismissAdminUsageNotification();
        }
    }
}
