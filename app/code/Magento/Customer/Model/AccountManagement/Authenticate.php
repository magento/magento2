<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
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
     * @var CustomerRepository
     */
    private CustomerRepository $customerRepository;

    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

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
     * @param CustomerRepository $customerRepository
     * @param CustomerFactory $customerFactory
     * @param AuthenticationInterface $authentication
     * @param AccountConfirmation $accountConfirmation
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerFactory $customerFactory,
        AuthenticationInterface $authentication,
        AccountConfirmation $accountConfirmation,
        ManagerInterface $eventManager
    ) {
        $this->customerRepository = $customerRepository;
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
            $customer = $this->customerRepository->get($email);
        } catch (NoSuchEntityException $exception) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

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

        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
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
