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

namespace Magento\User\Test\TestCase;

use Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Dashboard;
use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\User\Test\Fixture\AdminUserInjectable;

/**
 * Class LoginUserTest
 * Tests login to backend
 *
 */
class LoginUserTest extends Injectable
{
    /**
     * @var AdminAuthLogin
     */
    protected $loginPage;

    /**
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * @param AdminAuthLogin $loginPage
     * @param Dashboard $dashboard
     */
    public function __inject(AdminAuthLogin $loginPage, Dashboard $dashboard)
    {
        $this->loginPage = $loginPage;
        $this->dashboard = $dashboard;
    }

    /**
     * Log out if the admin user is already logged in.
     */
    protected function setUp()
    {
        $this->dashboard->getAdminPanelHeader()->logOut();
    }

    /**
     * Test admin login to backend
     *
     * @param AdminUserInjectable $user
     */
    public function test(AdminUserInjectable $user)
    {
        // Steps
        $this->loginPage->open();
        $this->loginPage->getLoginBlock()->fill($user);
        $this->loginPage->getLoginBlock()->submit();
    }
}
