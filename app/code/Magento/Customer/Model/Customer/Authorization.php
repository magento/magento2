<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Customer;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\AuthorizationInterface;
use Magento\Integration\Api\AuthorizationServiceInterface as AuthorizationService;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Checks if customer is logged in and authorized in the current store
 */
class Authorization implements AuthorizationInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Authorization constructor.
     *
     * @param UserContextInterface $userContext
     * @param CustomerFactory $customerFactory
     * @param CustomerResource $customerResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerFactory $customerFactory,
        CustomerResource $customerResource,
        StoreManagerInterface $storeManager
    ) {
        $this->userContext = $userContext;
        $this->customerFactory = $customerFactory;
        $this->customerResource = $customerResource;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function isAllowed($resource, $privilege = null)
    {
        if ($resource === AuthorizationService::PERMISSION_SELF
            && $this->userContext->getUserId()
            && $this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
        ) {
            $customer = $this->customerFactory->create();
            $this->customerResource->load($customer, $this->userContext->getUserId());
            $currentStoreId = $this->storeManager->getStore()->getId();
            $sharedStoreIds = $customer->getSharedStoreIds();

            return in_array($currentStoreId, $sharedStoreIds);
        }

        return false;
    }
}
