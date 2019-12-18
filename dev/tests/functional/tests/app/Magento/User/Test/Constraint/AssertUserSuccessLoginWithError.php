<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\TestStep\LoginUserOnBackendWithErrorStep;

/**
 * Verify whether customer has logged in to the Backend with error alert.
 */
class AssertUserSuccessLoginWithError extends AssertUserSuccessLogin
{
    /**
     * @var string
     */
    protected $loginStep = LoginUserOnBackendWithErrorStep::class;
}
