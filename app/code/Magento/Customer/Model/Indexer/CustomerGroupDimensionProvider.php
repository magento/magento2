<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\Customer\Model\Indexer;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;

<<<<<<< HEAD
=======
/**
 * Class CustomerGroupDimensionProvider
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class CustomerGroupDimensionProvider implements DimensionProviderInterface
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

<<<<<<< HEAD
=======
    /**
     * @param CustomerGroupCollectionFactory $collectionFactory
     * @param DimensionFactory $dimensionFactory
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    public function __construct(CustomerGroupCollectionFactory $collectionFactory, DimensionFactory $dimensionFactory)
    {
        $this->dimensionFactory = $dimensionFactory;
        $this->collectionFactory = $collectionFactory;
    }

<<<<<<< HEAD
=======
    /**
     * @inheritdoc
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    public function getIterator(): \Traversable
    {
        foreach ($this->getCustomerGroups() as $customerGroup) {
            yield $this->dimensionFactory->create(self::DIMENSION_NAME, (string)$customerGroup);
        }
    }

    /**
<<<<<<< HEAD
=======
     * Get Customer Groups
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return array
     */
    private function getCustomerGroups(): array
    {
        if ($this->customerGroupsDataIterator === null) {
            $customerGroups = $this->collectionFactory->create()->getAllIds();
            $this->customerGroupsDataIterator = is_array($customerGroups) ? $customerGroups : [];
        }

        return $this->customerGroupsDataIterator;
    }
}
