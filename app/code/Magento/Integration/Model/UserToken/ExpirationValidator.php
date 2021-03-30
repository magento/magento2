<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\UserToken;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\UserTokenValidatorInterface;

class ExpirationValidator implements UserTokenValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function validate(UserToken $token): void
    {
        if ($token->getData()->getExpires()->getTimestamp() <= time()) {
            throw new AuthorizationException(__('Consumer key has expired'));
        }
    }
}
