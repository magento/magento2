<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Integration\Api;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Integration\Api\Data\UserTokenParametersInterface;
use Magento\Integration\Model\UserToken\UserTokenParameters;
use Magento\Integration\Model\UserToken\UserTokenParametersFactory;

/**
 * Issues tokens used to authenticate users.
 */
class TokenManager
{
    /**
     * @var UserTokenParametersFactory
     */
    private $tokenParametersFactory;

    /**
     * @var UserTokenIssuerInterface
     */
    private $tokenIssuer;

    /**
     * @var UserTokenRevokerInterface
     */
    private $tokenRevoker;

    /**
     * @param UserTokenParametersFactory|null $tokenParamsFactory
     * @param UserTokenIssuerInterface|null $tokenIssuer
     * @param UserTokenRevokerInterface|null $tokenRevoker
     */
    public function __construct(
        ?UserTokenParametersFactory $tokenParamsFactory = null,
        ?UserTokenIssuerInterface $tokenIssuer = null,
        ?UserTokenRevokerInterface $tokenRevoker = null
    ) {
        $this->tokenParametersFactory = $tokenParamsFactory
            ?? ObjectManager::getInstance()->get(UserTokenParametersFactory::class);
        $this->tokenIssuer = $tokenIssuer ?? ObjectManager::getInstance()->get(UserTokenIssuerInterface::class);
        $this->tokenRevoker = $tokenRevoker ?? ObjectManager::getInstance()->get(UserTokenRevokerInterface::class);
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return UserTokenParameters
     */
    public function createUserTokenParameters(array $data = []): UserTokenParameters
    {
        return $this->tokenParametersFactory->create($data);
    }

    /**
     * Create token for a user.
     *
     * @param UserContextInterface $userContext
     * @param UserTokenParametersInterface $params
     * @return string
     */
    public function create(UserContextInterface $userContext, UserTokenParametersInterface $params): string
    {
        return $this->tokenIssuer->create($userContext, $params);
    }

    /**
     * Revoke all previously issued tokens for given user.
     *
     * @param UserContextInterface $userContext
     * @return void
     */
    public function revokeFor(UserContextInterface $userContext): void
    {
        $this->tokenRevoker->revokeFor($userContext);
    }
}
