<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\User\Test\Fixture\User;

/**
 * Asserts that user has only related permissions.
 */
class AssertUserRoleRestrictedAccess extends AbstractConstraint
{
    const DENIED_ACCESS = 'Access denied';

    /**
     * Asserts that user has only related permissions.
     *
     * @param BrowserInterface $browser
     * @param Dashboard $dashboard
     * @param User $user
     * @param array $restrictedAccess
     * @param string $denyUrl
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        Dashboard $dashboard,
        User $user,
        array $restrictedAccess,
        $denyUrl
    ) {
        $this->objectManager->create('Magento\User\Test\TestStep\LoginUserOnBackendStep', ['user' => $user])->run();

        $menuItems = $dashboard->getMenuBlock()->getTopMenuItems();
        \PHPUnit_Framework_Assert::assertEquals($menuItems, $restrictedAccess, 'Wrong display menu.');

        $browser->open($_ENV['app_backend_url'] . $denyUrl);
        $deniedMessage = $dashboard->getAccessDeniedBlock()->getTextFromAccessDeniedBlock();
        \PHPUnit_Framework_Assert::assertEquals(self::DENIED_ACCESS, $deniedMessage, 'Possible access to denied page.');
    }

    /**
     * Returns success message if assert true.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales item is present in Menu block.';
    }
}
