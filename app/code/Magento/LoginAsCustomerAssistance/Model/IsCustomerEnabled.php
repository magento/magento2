<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Check if customer is Enabled
 */
class IsCustomerEnabled
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Check if Customer is enabled by Customer id.
     *
     * @param int $customerId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(int $customerId): bool
    {
        if (!isset($this->registry[$customerId])) {
            $customer = $this->customerRepository->getById($customerId);
            $isActive = (bool)$customer->getExtensionAttributes()->getCompanyAttributes()->getStatus();
            $this->registry[$customerId] = $isActive;
        }

        return $this->registry[$customerId];
    }
}
