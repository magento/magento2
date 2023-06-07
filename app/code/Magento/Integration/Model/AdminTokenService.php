<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenIssuerInterface;
use Magento\Integration\Api\UserTokenRevokerInterface;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\User\Model\User as UserModel;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Integration\Model\UserToken\UserTokenParametersFactory;

/**
 * Class to handle token generation for Admins
 */
class AdminTokenService implements \Magento\Integration\Api\AdminTokenServiceInterface
{
    /**
     * User Model
     *
     * @var UserModel
     */
    private $userModel;

    /**
     * @var CredentialsValidator
     */
    private $validatorHelper;

    /**
     * @var RequestThrottler
     */
    private $requestThrottler;

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
     * Initialize service
     *
     * @param TokenModelFactory $tokenModelFactory
     * @param UserModel $userModel
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param CredentialsValidator $validatorHelper
     * @param UserTokenParametersFactory|null $tokenParamsFactory
     * @param UserTokenIssuerInterface|null $tokenIssuer
     * @param UserTokenRevokerInterface|null $tokenRevoker
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        UserModel $userModel,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper,
        ?UserTokenParametersFactory $tokenParamsFactory = null,
        ?UserTokenIssuerInterface $tokenIssuer = null,
        ?UserTokenRevokerInterface $tokenRevoker = null
    ) {
        $this->userModel = $userModel;
        $this->validatorHelper = $validatorHelper;
        $this->tokenParametersFactory = $tokenParamsFactory
            ?? ObjectManager::getInstance()->get(UserTokenParametersFactory::class);
        $this->tokenIssuer = $tokenIssuer ?? ObjectManager::getInstance()->get(UserTokenIssuerInterface::class);
        $this->tokenRevoker = $tokenRevoker ?? ObjectManager::getInstance()->get(UserTokenRevokerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function createAdminAccessToken($username, $password)
    {
        $this->validatorHelper->validate($username, $password);
        $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_ADMIN);
        $this->userModel->login($username, $password);
        if (!$this->userModel->getId()) {
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_ADMIN);
            /*
             * This message is same as one thrown in \Magento\Backend\Model\Auth to keep the behavior consistent.
             * Constant cannot be created in Auth Model since it uses legacy translation that doesn't support it.
             * Need to make sure that this is refactored once exception handling is updated in Auth Model.
             */
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
        $this->getRequestThrottler()->resetAuthenticationFailuresCount($username, RequestThrottler::USER_TYPE_ADMIN);
        $context = new CustomUserContext(
            (int) $this->userModel->getId(),
            CustomUserContext::USER_TYPE_ADMIN
        );
        $params = $this->tokenParametersFactory->create();

        return $this->tokenIssuer->create($context, $params);
    }

    /**
     * Revoke token by admin id.
     *
     * The function will delete the token from the oauth_token table.
     *
     * @param int $adminId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeAdminAccessToken($adminId)
    {
        try {
            $this->tokenRevoker->revokeFor(new CustomUserContext((int) $adminId, CustomUserContext::USER_TYPE_ADMIN));
        } catch (UserTokenException $exception) {
            throw new LocalizedException(__('The tokens couldn\'t be revoked.'), $exception);
        }
        return true;
    }

    /**
     * Get request throttler instance
     *
     * @return RequestThrottler
     * @deprecated 100.0.4
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }
}
