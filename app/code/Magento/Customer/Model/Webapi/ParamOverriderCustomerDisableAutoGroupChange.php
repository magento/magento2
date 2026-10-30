<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

/**
 * Replaces a "%customer_disable_auto_group_change%" value with the customer's stored value
 *
 * Keeps a customer from switching off the automatic group assignment (VAT ID validation) on their own account.
 */
class ParamOverriderCustomerDisableAutoGroupChange implements ParamOverriderInterface
{
    /**
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        private readonly UserContextInterface $userContext,
        private readonly CustomerRepositoryInterface $customerRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getOverriddenValue()
    {
        if ((int) $this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            return (int) $this->customerRepository->getById($this->userContext->getUserId())
                ->getDisableAutoGroupChange();
        }

        return null;
    }
}
