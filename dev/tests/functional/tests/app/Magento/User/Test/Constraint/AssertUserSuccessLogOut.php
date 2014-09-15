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

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\AdminAuthLogin;

/**
 * Class AssertUserSuccessLogOut
 */
class AssertUserSuccessLogOut extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Asserts that 'You have logged out.' message is present on page
     *
     * @param AdminAuthLogin $adminAuth
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(
        AdminAuthLogin $adminAuth,
        Dashboard $dashboard
    ) {
        $dashboard->getAdminPanelHeader()->logOut();
        $isLoginBlockVisible = $adminAuth->getLoginBlock()->isVisible();
        \PHPUnit_Framework_Assert::assertTrue(
            $isLoginBlockVisible,
            'Admin user was not logged out.'
        );
    }

    /**
     * Return message if user successful logout
     *
     * @return string
     */
    public function toString()
    {
        return 'User had successfully logged out.';
    }
}
