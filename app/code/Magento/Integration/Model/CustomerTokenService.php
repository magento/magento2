<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\TokenManager;
use Magento\Integration\Api\Exception\UserTokenException;
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
     * @var RequestThrottler
     */
    private $requestThrottler;

    /**
     * Initialize service
     *
     * @param AccountManagementInterface $accountManagement
     * @param CredentialsValidator $validatorHelper
     * @param TokenManager $tokenManager
     * @param ManagerInterface|null $eventManager
     */
    public function __construct(
        private readonly AccountManagementInterface $accountManagement,
        private readonly CredentialsValidator $validatorHelper,
        private readonly TokenManager $tokenManager,
        ?ManagerInterface $eventManager = null
    ) {
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(ManagerInterface::class);
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
        } catch (EmailNotConfirmedException $exception) {
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_CUSTOMER);
            throw $exception;
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
        $params = $this->tokenManager->createUserTokenParameters();

        return $this->tokenManager->create($context, $params);
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
            $this->tokenManager->revokeFor(
                new CustomUserContext((int)$customerId, CustomUserContext::USER_TYPE_CUSTOMER)
            );
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
     * @see no alternatives
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }
}
