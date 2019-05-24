<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\User\Test\Constraint;

/**
 * @inheritdoc
 */
class AssertUserRoleRestrictedAccessWithError extends AssertUserRoleRestrictedAccess
{
    protected $loginStep = 'Magento\User\Test\TestStep\LoginUserOnBackendWithErrorStep';
}
