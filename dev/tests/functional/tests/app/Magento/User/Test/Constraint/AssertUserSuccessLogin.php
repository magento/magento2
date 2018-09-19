<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\User\Test\Fixture\User;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Verify whether customer has logged in to the Backend.
 */
class AssertUserSuccessLogin extends AbstractConstraint
{
    /**
     * Verify whether customer has logged in to the Backend.
     *
     * @param User $user
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(User $user, Dashboard $dashboard)
    {
        $this->objectManager->create(
            \Magento\User\Test\TestStep\LoginUserOnBackendStep::class,
            ['user' => $user]
        )->run();
        \PHPUnit\Framework\Assert::assertTrue(
            $dashboard->getAdminPanelHeader()->isLoggedIn(),
            'Admin user was not logged in.'
        );
    }

    /**
     * Returns success message if equals to expected message.
     *
     * @return string
     */
    public function toString()
    {
        return 'Admin user is logged in.';
    }
}
