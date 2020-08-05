<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\LoginAsCustomerAssistance\Api\IsAssistanceEnabledInterface;
use Magento\LoginAsCustomerAssistance\Model\ResourceModel\GetLoginAsCustomerAssistanceAllowed;

/**
 * Check if customer allows Login as Customer assistance.
 */
class IsAssistanceEnabled implements IsAssistanceEnabledInterface
{
    /**
     * @var array
     */
    private $registry = [];

    /**
     * Merchant assistance denied by customer status code.
     */
    public const DENIED = 1;

    /**
     * Merchant assistance allowed by customer status code.
     */
    public const ALLOWED = 2;

    /**
     * @var CustomerExtensionFactory
     */
    private $customerExtensionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var GetLoginAsCustomerAssistanceAllowed
     */
    private $getLoginAsCustomerAssistanceAllowed;

    /**
     * @param CustomerExtensionFactory $customerExtensionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param GetLoginAsCustomerAssistanceAllowed $getLoginAsCustomerAssistanceAllowed
     */
    public function __construct(
        CustomerExtensionFactory $customerExtensionFactory,
        CustomerRepositoryInterface $customerRepository,
        GetLoginAsCustomerAssistanceAllowed $getLoginAsCustomerAssistanceAllowed
    ) {
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->customerRepository = $customerRepository;
        $this->getLoginAsCustomerAssistanceAllowed = $getLoginAsCustomerAssistanceAllowed;
    }

    /**
     * Check if customer allows Login as Customer assistance by Customer id.
     *
     * @param int $customerId
     * @return bool
     */
    public function execute(int $customerId): bool
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $exception) {
            // do nothing
        }
        if (isset($customer)) {
            $extensionAttributes = $customer->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->customerExtensionFactory->create();
            }
            if ($extensionAttributes->getAssistanceAllowed() === null) {
                if (isset($this->registry[$customerId])) {
                    $assistanceAllowed = $this->registry[$customerId];
                } else {
                    $assistanceAllowed = $this->getLoginAsCustomerAssistanceAllowed->execute($customerId);
                    $this->registry[$customerId] = $assistanceAllowed;
                }
                $extensionAttributes->setAssistanceAllowed($this->resolveStatus($assistanceAllowed));
                $customer->setExtensionAttributes($extensionAttributes);
            }
            $assistanceAllowed = $this->resolveAllowance($customer->getExtensionAttributes()->getAssistanceAllowed());
        } else {
            $assistanceAllowed = false;
        }

        return $assistanceAllowed;
    }

    /**
     * Get integer status value from boolean.
     *
     * @param bool $assistanceAllowed
     * @return int
     */
    private function resolveStatus(bool $assistanceAllowed): int
    {
        return $assistanceAllowed ? self::ALLOWED : self::DENIED;
    }

    /**
     * Get boolean status value from integer.
     *
     * @param int $statusCode
     * @return bool
     */
    private function resolveAllowance(int $statusCode): bool
    {
        return $statusCode === self::ALLOWED;
    }
}
