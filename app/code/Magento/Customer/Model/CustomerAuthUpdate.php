<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer Authentication update model.
 */
class CustomerAuthUpdate
{
    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var CustomerResourceModel
     */
    protected $customerResourceModel;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param CustomerResourceModel $customerResourceModel
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        CustomerResourceModel $customerResourceModel,
        CustomerFactory $customerFactory
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Reset Authentication data for customer.
     *
     * @param int $customerId
     * @return $this
     * @throws NoSuchEntityException
     */
    public function saveAuth($customerId)
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
        $customerModel = $this->customerFactory->create();
        $this->customerResourceModel->load($customerModel, $customerId);
        $currentLockExpiresVal = $customerModel->getData('lock_expires');
        $newLockExpiresVal = $customerSecure->getData('lock_expires');

        $this->customerResourceModel->getConnection()->update(
            $this->customerResourceModel->getTable('customer_entity'),
            [
                'failures_num' => $customerSecure->getData('failures_num'),
                'first_failure' => $customerSecure->getData('first_failure'),
                'lock_expires' => $newLockExpiresVal,
            ],
            $this->customerResourceModel->getConnection()->quoteInto('entity_id = ?', $customerId)
        );

        if ($currentLockExpiresVal !== $newLockExpiresVal) {
            $customerModel->reindex();
        }

        return $this;
    }
}
