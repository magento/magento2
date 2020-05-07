<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Plugin\LoginAsCustomerApi\Api\AuthenticateCustomerInterface;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;
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
     * @param LogInterfaceFactory $logFactory
     * @param SaveLogsInterface $saveLogs
     * @param CustomerRepositoryInterface $customerRepository
     * @param UserInterfaceFactory $userFactory
     * @param User $userResource
     */
    public function __construct(
        LogInterfaceFactory $logFactory,
        SaveLogsInterface $saveLogs,
        CustomerRepositoryInterface $customerRepository,
        UserInterfaceFactory $userFactory,
        User $userResource
    ) {
        $this->logFactory = $logFactory;
        $this->saveLogs = $saveLogs;
        $this->customerRepository = $customerRepository;
        $this->userFactory = $userFactory;
        $this->userResource = $userResource;
    }

    /**
     * Log user authentication as customer.
     *
     * @param AuthenticateCustomerInterface $subject
     * @param void $result
     * @param AuthenticationDataInterface $data
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        AuthenticateCustomerInterface $subject,
        $result,
        AuthenticationDataInterface $data
    ): void {
        $customerId = $data->getCustomerId();
        $customerEmail = $this->customerRepository->getById($customerId)->getEmail();
        $userId = $data->getAdminId();
        $user = $this->userFactory->create();
        $this->userResource->load($user, $userId);
        $log = $this->logFactory->create(
            [
                'data' => [
                    'customer_id' => $customerId,
                    'user_id' => $userId,
                    'customer_email' => $customerEmail,
                    'user_name' => $user->getUserName(),
                ],
            ]
        );
        $this->saveLogs->execute([$log]);
    }
}
