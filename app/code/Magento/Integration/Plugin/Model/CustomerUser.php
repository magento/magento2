<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Plugin\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Integration\Model\CustomerTokenService;
use Magento\Customer\Model\Customer;

/**
 * Plugin to delete customer tokens when customer becomes inactive
 */
class CustomerUser
{
    /**
     * @var CustomerTokenService
     */
    private $customerTokenService;

    /**
     * @param CustomerTokenService $customerTokenService
     */
    public function __construct(
        CustomerTokenService $customerTokenService
    ) {
        $this->customerTokenService = $customerTokenService;
    }

    /**
     * Check if customer is inactive - if so, invalidate their tokens
     *
     * @param Customer $subject
     * @param AbstractModel $object
     * @return AbstractModel
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        Customer $subject,
        AbstractModel $object
    ) {
        $isActive = $object->getIsActive();
        if (isset($isActive) && $isActive == 0) {
            $this->customerTokenService->revokeCustomerAccessToken($object->getId());
        }
        return $object;
    }

    public function afterDelete(Customer $subject, AbstractModel $return): AbstractModel
    {
        $this->customerTokenService->revokeCustomerAccessToken((int) $subject->getId());

        return $return;
    }
}
