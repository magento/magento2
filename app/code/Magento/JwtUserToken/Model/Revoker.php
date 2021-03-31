<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenRevokerInterface;

class Revoker implements UserTokenRevokerInterface
{
    /**
     * @inheritDoc
     */
    public function revokeFor(UserContextInterface $userContext): void
    {
        // TODO: Implement revokeFor() method.
    }
}
