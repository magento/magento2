<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;

/**
 * Observer to execute upgrading customer password hash when customer has logged in.
 */
class UpgradeCustomerPasswordObserver implements ObserverInterface
{
    /**
     * Encryption model
     *
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param EncryptorInterface $encryptor
     * @param CustomerRegistry $customerRegistry
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        EncryptorInterface $encryptor,
        CustomerRegistry $customerRegistry,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->encryptor = $encryptor;
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Upgrade customer password hash when customer has logged in.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $password = $observer->getEvent()->getData('password');
        /** @var \Magento\Customer\Model\Customer $model */
        $model = $observer->getEvent()->getData('model');
        $customer = $this->customerRepository->getById($model->getId());
        $customerSecure = $this->customerRegistry->retrieveSecureData($model->getId());

        if (!$this->encryptor->validateHashVersion($customerSecure->getPasswordHash(), true)) {
            $customerSecure->setPasswordHash($this->encryptor->getHash($password, true));
            // No need to validate customer and customer address while upgrading customer password
            $this->setIgnoreValidationFlag($customer);
            $this->customerRepository->save($customer);
        }
    }

    /**
     * Set ignore_validation_flag to skip unnecessary address and customer validation.
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function setIgnoreValidationFlag(CustomerInterface $customer)
    {
        $customer->setData('ignore_validation_flag', true);
    }
}
