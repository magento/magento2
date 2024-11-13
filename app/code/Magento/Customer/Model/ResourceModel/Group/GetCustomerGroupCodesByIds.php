<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\Group;

/**
 * Provides customer group codes for given customer group IDs
 */
class GetCustomerGroupCodesByIds
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Returns customer group codes indexed by their IDs
     *
     * @param array $customerGroupIds
     * @return array
     */
    public function execute(array $customerGroupIds): array
    {
        $result = [];
        if ($customerGroupIds) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(
                'customer_group_id',
                ['in' => array_map('intval', array_unique($customerGroupIds))]
            );
            $collection->addFieldToSelect('customer_group_id');
            $collection->addFieldToSelect('customer_group_code');
            $result = array_column($collection->getData(), 'customer_group_code', 'customer_group_id');
        }
        return $result;
    }
}
