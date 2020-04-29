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
     * @param LogInterfaceFactory $logFactory
     * @param SaveLogsInterface $saveLogs
     * @param CustomerRepositoryInterface $customerRepository
     * @param UserInterfaceFactory $userFactory
     */
    public function __construct(
        LogInterfaceFactory $logFactory,
        SaveLogsInterface $saveLogs,
        CustomerRepositoryInterface $customerRepository,
        UserInterfaceFactory $userFactory
    ) {
        $this->logFactory = $logFactory;
        $this->saveLogs = $saveLogs;
        $this->customerRepository = $customerRepository;
        $this->userFactory = $userFactory;
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
        $userName = $this->userFactory->create()->load($userId)->getUserName();
        $log = $this->logFactory->create();
        $log->setCustomerId($customerId);
        $log->setUserId($userId);
        $log->setCustomerEmail($customerEmail);
        $log->setUserName($userName);
        $this->saveLogs->execute([$log]);
    }
}
