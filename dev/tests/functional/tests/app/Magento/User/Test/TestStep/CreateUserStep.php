<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\User\Test\Fixture\User;

/**
 * Create new user.
 */
class CreateUserStep implements TestStepInterface
{
    /**
     * User fixture.
     *
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Run step flow.
     *
     * @return array
     */
    public function run()
    {
        $this->user->persist();

        return ['user' => $this->user];
    }
}
