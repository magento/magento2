<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Test\Handler\Ui;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Ui;
use Mtf\Factory\Factory;

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
