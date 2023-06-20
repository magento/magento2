<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\UserToken\UserTokenParametersFactory;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenIssuerInterface;
use Magento\Integration\Api\UserTokenRevokerInterface;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * @inheritdoc
 */
class CustomerTokenService implements CustomerTokenServiceInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * Customer Account Service
     *
     * @var AccountManagementInterface
     */
    private $accountManagement;

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
     * @param AccountManagementInterface $accountManagement
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param CredentialsValidator $validatorHelper
     * @param ManagerInterface|null $eventManager
     * @param UserTokenParametersFactory|null $tokenParamsFactory
     * @param UserTokenIssuerInterface|null $tokenIssuer
     * @param UserTokenRevokerInterface|null $tokenRevoker
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        AccountManagementInterface $accountManagement,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper,
        ManagerInterface $eventManager = null,
        ?UserTokenParametersFactory $tokenParamsFactory = null,
        ?UserTokenIssuerInterface $tokenIssuer = null,
        ?UserTokenRevokerInterface $tokenRevoker = null
    ) {
        $this->accountManagement = $accountManagement;
        $this->validatorHelper = $validatorHelper;
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(ManagerInterface::class);
        $this->tokenParametersFactory = $tokenParamsFactory
            ?? ObjectManager::getInstance()->get(UserTokenParametersFactory::class);
        $this->tokenIssuer = $tokenIssuer ?? ObjectManager::getInstance()->get(UserTokenIssuerInterface::class);
        $this->tokenRevoker = $tokenRevoker ?? ObjectManager::getInstance()->get(UserTokenRevokerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function createCustomerAccessToken($username, $password)
    {
        $this->validatorHelper->validate($username, $password);
        $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_CUSTOMER);
        try {
            $customerDataObject = $this->accountManagement->authenticate($username, $password);
        } catch (\Exception $e) {
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_CUSTOMER);
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
        $this->eventManager->dispatch('customer_login', ['customer' => $customerDataObject]);
        $this->getRequestThrottler()->resetAuthenticationFailuresCount($username, RequestThrottler::USER_TYPE_CUSTOMER);
        $context = new CustomUserContext(
            (int)$customerDataObject->getId(),
            CustomUserContext::USER_TYPE_CUSTOMER
        );
        $params = $this->tokenParametersFactory->create();

        return $this->tokenIssuer->create($context, $params);
    }

    /**
     * Revoke token by customer id.
     *
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeCustomerAccessToken($customerId)
    {
        try {
            $this->tokenRevoker->revokeFor(new CustomUserContext((int)$customerId, CustomUserContext::USER_TYPE_CUSTOMER));
        } catch (UserTokenException $exception) {
            throw new LocalizedException(__('Failed to revoke customer\'s access tokens'), $exception);
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
            return ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }
}
