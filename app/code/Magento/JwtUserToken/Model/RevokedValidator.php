<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\UserTokenValidatorInterface;
use Magento\JwtUserToken\Api\RevokedRepositoryInterface;

/**
 * Verifies that tokens were not revoked.
 */
class RevokedValidator implements UserTokenValidatorInterface
{
    /**
     * @var RevokedRepositoryInterface
     */
    private $revokedRepo;

    /**
     * @param RevokedRepositoryInterface $revokedRepo
     */
    public function __construct(RevokedRepositoryInterface $revokedRepo)
    {
        $this->revokedRepo = $revokedRepo;
    }

    /**
     * @inheritDoc
     */
    public function validate(UserToken $token): void
    {
        $revoked = $this->revokedRepo->findRevoked(
            (int) $token->getUserContext()->getUserType(),
            (int) $token->getUserContext()->getUserId()
        );

        if ($revoked && $token->getData()->getIssued()->getTimestamp() <= $revoked->getBeforeTimestamp()) {
            throw new AuthorizationException(__('User token has been revoked'));
        }
    }
}
