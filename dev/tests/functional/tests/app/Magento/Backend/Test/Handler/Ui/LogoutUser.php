<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Handler\Ui;

use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Ui;

/**
 * Class LogoutUser
 * Handler for ui backend user logout
 *
 */
class LogoutUser extends Ui
{
    /**
     * Logout admin user
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed|string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $homePage = Factory::getPageFactory()->getAdminDashboard();
        $headerBlock = $homePage->getAdminPanelHeader();
        $headerBlock->logOut();
    }
}
