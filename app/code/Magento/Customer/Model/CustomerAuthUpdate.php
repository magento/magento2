<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

/**
 * Customer Authentication update model.
 * @since 2.1.1
 */
class CustomerAuthUpdate
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     * @since 2.1.1
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     * @since 2.1.1
     */
    protected $customerResourceModel;

    /**
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel
     * @since 2.1.1
     */
    public function __construct(
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->customerResourceModel = $customerResourceModel;
    }

    /**
     * Reset Authentication data for customer.
     *
     * @param int $customerId
     * @return $this
     * @since 2.1.1
     */
    public function saveAuth($customerId)
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);

        $this->customerResourceModel->getConnection()->update(
            $this->customerResourceModel->getTable('customer_entity'),
            [
                'failures_num' => $customerSecure->getData('failures_num'),
                'first_failure' => $customerSecure->getData('first_failure'),
                'lock_expires' => $customerSecure->getData('lock_expires'),
            ],
            $this->customerResourceModel->getConnection()->quoteInto('entity_id = ?', $customerId)
        );

        return $this;
    }
}
