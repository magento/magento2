<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\LoginAsCustomerAssistance\Api\SetAssistanceInterface;
use Magento\LoginAsCustomerAssistance\Model\ResourceModel\DeleteLoginAsCustomerAssistanceAllowed;
use Magento\LoginAsCustomerAssistance\Model\ResourceModel\SaveLoginAsCustomerAssistanceAllowed;

/**
 * @inheritdoc
 */
class SetAssistance implements SetAssistanceInterface
{
    /**
     * @var array
     */
    private $registry = [];

    /**
     * @var CustomerExtensionFactory
     */
    private $customerExtensionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var DeleteLoginAsCustomerAssistanceAllowed
     */
    private $deleteLoginAsCustomerAssistanceAllowed;

    /**
     * @var SaveLoginAsCustomerAssistanceAllowed
     */
    private $saveLoginAsCustomerAssistanceAllowed;

    /**
     * @param CustomerExtensionFactory $customerExtensionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param DeleteLoginAsCustomerAssistanceAllowed $deleteLoginAsCustomerAssistanceAllowed
     * @param SaveLoginAsCustomerAssistanceAllowed $saveLoginAsCustomerAssistanceAllowed
     */
    public function __construct(
        CustomerExtensionFactory $customerExtensionFactory,
        CustomerRepositoryInterface $customerRepository,
        DeleteLoginAsCustomerAssistanceAllowed $deleteLoginAsCustomerAssistanceAllowed,
        SaveLoginAsCustomerAssistanceAllowed $saveLoginAsCustomerAssistanceAllowed
    ) {
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->customerRepository = $customerRepository;
        $this->deleteLoginAsCustomerAssistanceAllowed = $deleteLoginAsCustomerAssistanceAllowed;
        $this->saveLoginAsCustomerAssistanceAllowed = $saveLoginAsCustomerAssistanceAllowed;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $customerId, bool $isEnabled): void
    {
        if ($this->isUpdateRequired($customerId, $isEnabled)) {
            if ($isEnabled) {
                $this->saveLoginAsCustomerAssistanceAllowed->execute($customerId);
            } else {
                $this->deleteLoginAsCustomerAssistanceAllowed->execute($customerId);
            }
            $this->updateRegistry($customerId, $isEnabled);
        }
    }

    /**
     * Check if 'assistance_allowed' cached value differs from actual.
     *
     * @param int $customerId
     * @param bool $isEnabled
     * @return bool
     */
    private function isUpdateRequired(int $customerId, bool $isEnabled): bool
    {
        return !isset($this->registry[$customerId]) || $this->registry[$customerId] !== $isEnabled;
    }

    /**
     * Update 'assistance_allowed' cached value.
     *
     * @param int $customerId
     * @param bool $isEnabled
     */
    private function updateRegistry(int $customerId, bool $isEnabled): void
    {
        $this->registry[$customerId] = $isEnabled;
    }
}
