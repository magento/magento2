<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Handler\Ui;

use Mtf\Factory\Factory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Ui;

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

        $loginPage = Factory::getPageFactory()->getAdminAuthLogin();
        $loginForm = $loginPage->getLoginBlock();

        $adminHeaderPanel = $loginPage->getHeaderBlock();
        if (!$adminHeaderPanel || !$adminHeaderPanel->isVisible()) {
            $loginPage->open();
            if ($adminHeaderPanel->isVisible()) {
                return;
            }
            $loginForm->fill($fixture);
            $loginForm->submit();
            $loginPage->waitForHeaderBlock();
        }
    }
}
