<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Authenticate customer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authenticate
{
    /**
     * @var CustomerRegistry
     */
    private CustomerRegistry $customerRegistry;

    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory
     */
    private CustomerInterfaceFactory $customerFactory;

    /**
     * @var AuthenticationInterface
     */
    private AuthenticationInterface $authentication;

    /**
     * @var AccountConfirmation
     */
    private AccountConfirmation $accountConfirmation;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerInterfaceFactory $customerFactory
     * @param AuthenticationInterface $authentication
     * @param AccountConfirmation $accountConfirmation
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        DataObjectHelper $dataObjectHelper,
        CustomerInterfaceFactory $customerFactory,
        AuthenticationInterface $authentication,
        AccountConfirmation $accountConfirmation,
        ManagerInterface $eventManager
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerFactory = $customerFactory;
        $this->authentication = $authentication;
        $this->accountConfirmation = $accountConfirmation;
        $this->eventManager = $eventManager;
    }

    /**
     * Authenticate a customer by username and password
     *
     * @param string $email
     * @param string $password
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function execute(string $email, string $password): CustomerInterface
    {
        try {
            $customerModel = $this->customerRegistry->retrieveByEmail($email);
        } catch (NoSuchEntityException $exception) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        $customer = $this->getCustomerDataObject($customerModel);

        $customerId = $customer->getId();
        if ($this->authentication->isLocked($customerId)) {
            throw new UserLockedException(__('The account is locked.'));
        }
        try {
            $this->authentication->authenticate($customerId, $password);
        } catch (InvalidEmailOrPasswordException $exception) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        if ($customer->getConfirmation()
            && ($this->isConfirmationRequired($customer) || $this->isEmailChangedConfirmationRequired($customer))) {
            throw new EmailNotConfirmedException(__('This account isn\'t confirmed. Verify and try again.'));
        }

        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }

    /**
     * Convert custom model to DTO
     *
     * @param Customer $customerModel
     * @return CustomerInterface
     */
    private function getCustomerDataObject(Customer $customerModel): CustomerInterface
    {
        $customerDataObject = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $customerModel->getData(),
            CustomerInterface::class
        );
        $customerDataObject->setId($customerModel->getId());
        return $customerDataObject;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    private function isConfirmationRequired($customer)
    {
        return $this->accountConfirmation->isConfirmationRequired(
            $customer->getWebsiteId(),
            $customer->getId(),
            $customer->getEmail()
        );
    }

    /**
     * Checks if account confirmation is required if the email address has been changed
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    private function isEmailChangedConfirmationRequired(CustomerInterface $customer): bool
    {
        return $this->accountConfirmation->isEmailChangedConfirmationRequired(
            (int)$customer->getWebsiteId(),
            (int)$customer->getId(),
            $customer->getEmail()
        );
    }
}
