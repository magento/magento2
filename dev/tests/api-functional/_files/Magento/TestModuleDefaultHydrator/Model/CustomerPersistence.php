<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestModuleDefaultHydrator\Model;

use Magento\TestModuleDefaultHydrator\Api\CustomerPersistenceInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;

class CustomerPersistence implements CustomerPersistenceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerDataFactory;

    public function __construct(
        EntityManager $entityManager,
        CustomerInterfaceFactory $customerDataFactory
    ) {
        $this->entityManager = $entityManager;
        $this->customerDataFactory = $customerDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return $this->entityManager->save($customer);
    }

    /**
     * {@inheritdoc}
     */
    public function get($email, $websiteId = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id, $websiteId = null)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerDataFactory->create();
        $entity = $this->entityManager->load($customer, $id);
        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerDataFactory->create();
        $customer = $this->entityManager->load($customer, $id);
        try {
            $this->entityManager->delete($customer);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
