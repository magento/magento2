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
use Magento\JwtUserToken\Api\Data\Revoked;
use Magento\JwtUserToken\Api\RevokedRepositoryInterface;

class Revoker implements UserTokenRevokerInterface
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
    public function revokeFor(UserContextInterface $userContext): void
    {
        //Invalidating all tokens issued before current datetime.
        $this->revokedRepo->saveRevoked(
            new Revoked((int) $userContext->getUserType(), (int) $userContext->getUserId(), time())
        );
    }
}
