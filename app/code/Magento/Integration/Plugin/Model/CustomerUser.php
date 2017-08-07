<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Plugin\Model;

use Magento\Integration\Model\CustomerTokenService;

/**
 * Plugin to delete customer tokens when customer becomes inactive
 * @since 2.1.7
 */
class CustomerUser
{
    /**
     * @var CustomerTokenService
     * @since 2.1.7
     */
    private $customerTokenService;

    /**
     * @param CustomerTokenService $customerTokenService
     * @since 2.1.7
     */
    public function __construct(
        CustomerTokenService $customerTokenService
    ) {
        $this->customerTokenService = $customerTokenService;
    }

    /**
     * Check if customer is inactive - if so, invalidate their tokens
     *
     * @param \Magento\Customer\Model\Customer $subject
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.1.7
     */
    public function afterSave(
        \Magento\Customer\Model\Customer $subject,
        \Magento\Framework\DataObject $object
    ) {
        $isActive = $object->getIsActive();
        if (isset($isActive) && $isActive == 0) {
            $this->customerTokenService->revokeCustomerAccessToken($object->getId());
        }
        return $subject;
    }
}
