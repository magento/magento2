<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\User\Test\Fixture\User;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Create custom admin with custom role.
 *
 * Steps:
 * 1. Login to the admin panel with the newly created admin user.
 * 2. Perform all assertions.
 *
 * @ZephyrId MAGETWO-41214, MAGETWO-58541, MAGETWO-47568
 */
class CustomAclPermissionTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    protected $testStepFactory;

    /**
     * Setup necessary data for test.
     *
     * @param TestStepFactory $testStepFactory
     * @return void
     */
    public function __inject(
        TestStepFactory $testStepFactory
    ) {
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Test acl permissions.
     *
     * @param User $user
     * @return array
     */
    public function test(User $user)
    {
        $user->persist();
        $this->testStepFactory->create(
            \Magento\User\Test\TestStep\LoginUserOnBackendStep::class,
            ['user' => $user]
        )->run();
    }
}
