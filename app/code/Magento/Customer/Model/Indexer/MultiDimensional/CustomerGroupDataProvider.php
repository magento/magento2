<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Indexer\MultiDimensional;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;

class CustomerGroupDataProvider implements DimensionProviderInterface
{
    /**
     * Name for customer group dimension for multidimensional indexer
     * 'cg' - stands for 'customer_group'
     */
    const DIMENSION_NAME = 'cg';

    /**
     * @var CustomerGroupCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \SplFixedArray
     */
    private $customerGroupsDataIterator;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    public function __construct(CustomerGroupCollectionFactory $collectionFactory, DimensionFactory $dimensionFactory)
    {
        $this->dimensionFactory = $dimensionFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->getCustomerGroups() as $customerGroup) {
            yield $this->dimensionFactory->create(self::DIMENSION_NAME, (string)$customerGroup);
        }
    }

    /**
     * @return \SplFixedArray
     */
    private function getCustomerGroups()
    {
        if ($this->customerGroupsDataIterator === null) {
            $this->customerGroupsDataIterator = \SplFixedArray::fromArray(
                $this->collectionFactory->create()->getAllIds(),
                false
            );
        }

        return $this->customerGroupsDataIterator;
    }
}
