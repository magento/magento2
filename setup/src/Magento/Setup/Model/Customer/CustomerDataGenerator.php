<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Customer;

/**
 * Generate customer data for customer fixture
 */
class CustomerDataGenerator
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var \Magento\Setup\Model\Address\AddressDataGenerator
     */
    private $addressDataGenerator;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var array
     */
    private $customerGroupIds;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory
     * @param \Magento\Setup\Model\Address\AddressDataGenerator $addressDataGenerator
     * @param array $config
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Setup\Model\Address\AddressDataGenerator $addressDataGenerator,
        array $config
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->addressDataGenerator = $addressDataGenerator;
        $this->config = $config;
    }

    /**
     * Generate customer data by index
     *
     * @param int $customerId
     * @return array
     */
    public function generate($customerId)
    {
        return [
            'customer' => [
                'email' => sprintf('user_%s@example.com', $customerId),
                'group_id' => $this->getGroupIdForCustomer($customerId)
            ],

            'addresses' => $this->generateAddresses(),
        ];
    }

    /**
     * Get customer group id for customer
     * @param int $customerId
     * @return int
     */
    private function getGroupIdForCustomer($customerId)
    {
        if (!$this->customerGroupIds) {
            $this->customerGroupIds = $this->groupCollectionFactory->create()->getAllIds();
        }

        return $this->customerGroupIds[$customerId % count($this->customerGroupIds)];
    }

    /**
     * Generate customer addresses with distribution
     * 50% as shipping address
     * 50% as billing address
     *
     * @return array
     */
    private function generateAddresses()
    {
        $addresses = [];
        $addressesCount = $this->config['addresses-count'];

        while ($addressesCount) {
            $addresses[] = $this->addressDataGenerator->generateAddress();
            $addressesCount--;
        }

        return $addresses;
    }
}
