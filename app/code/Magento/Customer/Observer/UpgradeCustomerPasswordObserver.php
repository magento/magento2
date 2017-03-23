<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;

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
     * Upgrade customer password hash when customer has logged in
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
            $this->customerRepository->save($customer);
        }
    }
}
