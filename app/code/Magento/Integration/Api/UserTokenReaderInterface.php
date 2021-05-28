<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Api;

use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\Exception\UserTokenException;

/**
 * Reads user token data.
 */
interface UserTokenReaderInterface
{
    /**
     * Read user data from a token.
     *
     * @param string $token
     * @return UserToken
     * @throws UserTokenException
     */
    public function read(string $token): UserToken;
}
