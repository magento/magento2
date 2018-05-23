<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Indexer\MultiDimensional;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Framework\Indexer\MultiDimensional\DimensionFactory;
use Magento\Framework\Indexer\MultiDimensional\DimensionDataProviderInterface;

class CustomerGroupDataProvider implements DimensionDataProviderInterface, CustomerGroupDimensionInterface
{
    /**
     * @var \SplFixedArray
     */
    private $customerGroupsDataIterator;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    public function __construct(CustomerGroupCollectionFactory $collectionFactory, DimensionFactory $dimensionFactory) {
        $this->dimensionFactory = $dimensionFactory;
        $this->customerGroupsDataIterator = \SplFixedArray::fromArray(
            $collectionFactory->create()->load()->getAllIds()
        );
    }

    public function current()
    {
        return $this->dimensionFactory->create(self::DIMENSION_NAME, $this->customerGroupsDataIterator->current());
    }

    public function next()
    {
        $this->customerGroupsDataIterator->next();
    }

    public function key()
    {
        return $this->customerGroupsDataIterator->key();
    }

    public function valid()
    {
        return $this->customerGroupsDataIterator->valid();
    }

    public function rewind()
    {
        $this->customerGroupsDataIterator->rewind();
    }
}