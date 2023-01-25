<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Plugin\LoginAsCustomerApi;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface;
use Magento\LoginAsCustomerLog\Api\Data\LogInterfaceFactory;
use Magento\LoginAsCustomerLog\Api\SaveLogsInterface;
use Magento\User\Api\Data\UserInterfaceFactory;
use Magento\User\Model\ResourceModel\User;

/**
 * Log user logged in as customer plugin.
 */
class LogAuthenticationPlugin
{
    /**
     * @var GetAuthenticationDataBySecretInterface
     */
    private $getAuthenticationDataBySecret;

    /**
     * @var LogInterfaceFactory
     */
    private $logFactory;

    /**
     * @var SaveLogsInterface
     */
    private $saveLogs;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var UserInterfaceFactory
     */
    private $userFactory;

    /**
     * @var User
     */
    private $userResource;

    /**
     * @param GetAuthenticationDataBySecretInterface $getAuthenticationDataBySecret
     * @param LogInterfaceFactory $logFactory
     * @param SaveLogsInterface $saveLogs
     * @param CustomerRepositoryInterface $customerRepository
     * @param UserInterfaceFactory $userFactory
     * @param User $userResource
     */
    public function __construct(
        GetAuthenticationDataBySecretInterface $getAuthenticationDataBySecret,
        LogInterfaceFactory $logFactory,
        SaveLogsInterface $saveLogs,
        CustomerRepositoryInterface $customerRepository,
        UserInterfaceFactory $userFactory,
        User $userResource
    ) {
        $this->getAuthenticationDataBySecret = $getAuthenticationDataBySecret;
        $this->logFactory = $logFactory;
        $this->saveLogs = $saveLogs;
        $this->customerRepository = $customerRepository;
        $this->userFactory = $userFactory;
        $this->userResource = $userResource;
    }

    /**
     * Log user authentication as customer.
     *
     * @param AuthenticateCustomerBySecretInterface $subject
     * @param void $result
     * @param string $secret
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        AuthenticateCustomerBySecretInterface $subject,
        $result,
        string $secret
    ): void {
        $authenticationData = $this->getAuthenticationDataBySecret->execute($secret);

        $customerId = $authenticationData->getCustomerId();
        $customerEmail = $this->customerRepository->getById($customerId)->getEmail();

        $userId = $authenticationData->getAdminId();
        $user = $this->userFactory->create();
        $this->userResource->load($user, $userId);
        $userName = $user->getUserName();

        $log = $this->logFactory->create(
            [
                'data' => [
                    'customer_id' => $customerId,
                    'user_id' => $userId,
                    'customer_email' => $customerEmail,
                    'user_name' => $userName,
                ],
            ]
        );
        $this->saveLogs->execute([$log]);
    }
}
