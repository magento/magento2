<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Integration\Api;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Api\Exception\UserTokenException;

/**
 * Revokes user tokens.
 */
interface UserTokenRevokerInterface
{
    /**
     * Revoke all previously issued tokens for given user.
     *
     * @param UserContextInterface $userContext
     * @return void
     * @throws UserTokenException
     */
    public function revokeFor(UserContextInterface $userContext): void;
}
