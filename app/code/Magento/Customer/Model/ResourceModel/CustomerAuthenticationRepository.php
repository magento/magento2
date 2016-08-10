<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel;

/**
 * Customer repository.
 */
class CustomerAuthenticationRepository
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResourceModel;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
      */
    public function __construct(
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->customerResourceModel = $customerResourceModel;
        $this->eventManager = $eventManager;
    }

    /**
     * Create or update a customer.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function save(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());

        $this->customerResourceModel->getConnection()->update(
            $this->customerResourceModel->getTable('customer_entity'),
            array(
                'failures_num' => $customerSecure->getData('failures_num'),
                'first_failure' => $customerSecure->getData('first_failure'),
                'lock_expires' => $customerSecure->getData('lock_expires'),
            ),
            $this->customerResourceModel->getConnection()->quoteInto('entity_id = ?', $customer->getId())
        );

        $savedCustomer = $this->get($customer->getEmail(), $customer->getWebsiteId());
        $this->eventManager->dispatch(
            'customer_save_after_data_object',
            ['customer_data_object' => $savedCustomer, 'orig_customer_data_object' => $customer]
        );
        return $savedCustomer;
    }

    /**
     * Retrieve customer.
     *
     * @param string $email
     * @param int|null $websiteId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified email does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($email, $websiteId = null)
    {
        $customerModel = $this->customerRegistry->retrieveByEmail($email, $websiteId);
        return $customerModel->getDataModel();
    }

    /**
     * Get customer by customer ID.
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($customerId)
    {
        $customer = $this->customerRegistry->retrieve($customerId);
        return $customer->getDataModel();
    }
}
