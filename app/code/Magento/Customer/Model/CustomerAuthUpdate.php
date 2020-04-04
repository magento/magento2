<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Framework\App\ObjectManager;
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
     * @var Customer
     */
    private $customerModel;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param CustomerResourceModel $customerResourceModel
     * @param Customer|null $customerModel
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        CustomerResourceModel $customerResourceModel,
        Customer $customerModel = null
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerModel = $customerModel ?: ObjectManager::getInstance()->get(Customer::class);
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

        $this->customerResourceModel->load($this->customerModel, $customerId);
        $currentLockExpiresVal = $this->customerModel->getData('lock_expires');
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
            $this->customerModel->reindex();
        }

        return $this;
    }
}
