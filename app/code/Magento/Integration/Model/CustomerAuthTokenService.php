<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Event\ManagerInterface;

/**
 * @inheritdoc
 */
class CustomerAuthTokenService extends CustomerTokenService implements \Magento\Integration\Api\CustomerAuthTokenServiceInterface
{
    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     * @var Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Customer Account Service
     *
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    private $validatorHelper;

    /**
     * Initialize service
     *
     * @param TokenModelFactory $tokenModelFactory
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Integration\Model\CredentialsValidator $validatorHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        AccountManagementInterface $accountManagement,
        CredentialsValidator $validatorHelper,
        ManagerInterface $eventManager = null
    ) {
        parent::__construct(
            $tokenModelFactory,
            $accountManagement,
            $validatorHelper,
            $eventManager
        );
        $this->tokenModelFactory = $tokenModelFactory;
        $this->accountManagement = $accountManagement;
        $this->validatorHelper = $validatorHelper;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function createCustomerAuthToken($username, $password, $websiteId = null)
    {
        $this->validatorHelper->validate($username, $password);
        $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_CUSTOMER);
        try {
            $customerDataObject = $this->accountManagement->authenticate($username, $password, $websiteId);
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
        return $this->tokenModelFactory->create()->createCustomerToken($customerDataObject->getId())->getToken();
    }
}
