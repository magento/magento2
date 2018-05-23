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
            $collectionFactory->create()->getAllIds()
        );
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->customerGroupsDataIterator as $customerGroup) {
            yield $this->dimensionFactory->create(self::DIMENSION_NAME, $customerGroup);
        }
    }
}
