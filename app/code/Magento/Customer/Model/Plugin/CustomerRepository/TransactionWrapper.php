<?php
/**
 * Plugin for \Magento\Customer\Api\CustomerRepositoryInterface
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Plugin\CustomerRepository;

class TransactionWrapper
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $resourceModel;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer $resourceModel
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $passwordHash
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $passwordHash = null
    ) {
        $this->resourceModel->beginTransaction();
        try {
            /** @var $result \Magento\Customer\Api\Data\CustomerInterface */
            $result = $proceed($customer, $passwordHash);
            $this->resourceModel->commit();
            return $result;
        } catch (\Exception $e) {
            $this->resourceModel->rollBack();
            throw $e;
        }
    }
}
