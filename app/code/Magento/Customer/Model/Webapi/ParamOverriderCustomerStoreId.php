<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

/**
 * Replaces a "%customer_store_id%" value with the real customer id
 */
class ParamOverriderCustomerStoreId implements ParamOverriderInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(UserContextInterface $userContext, CustomerRepositoryInterface $customerRepository)
    {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function getOverriddenValue()
    {
        if ((int) $this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            return $this->customerRepository->getById($this->userContext->getUserId())->getStoreId();
        }

        return null;
    }
}
