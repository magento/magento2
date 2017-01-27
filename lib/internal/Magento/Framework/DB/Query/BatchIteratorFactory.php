<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Query;

/**
 * Factory class for \Magento\Framework\DB\Query\BatchIterator, \Magento\Framework\DB\Query\BatchRangeIterator
 *
 * @see \Magento\Framework\DB\Query\BatchIterator
 * @see \Magento\Framework\DB\Query\BatchRangeIterator
 */
class BatchIteratorFactory
{
    /**
     * Constant which determine strategy to create iterator which will to process
     * range field eg. entity_id with unique values.
     */
    const UNIQUE_FIELD_ITERATOR = "unique";

    /**
     * Constant which determine strategy to create iterator which will to process
     * range field with non-unique values.
     */
    const NON_UNIQUE_FIELD_ITERATOR = "non_unqiue";

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var string
     */
    private $uniqueIteratorInstanceName;

    /**
     * @var string
     */
    private $nonUniqueIteratorInstanceName;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\DB\Query\BatchIterator $uniqueIteratorInstanceName
     * @param \Magento\Framework\DB\Query\BatchRangeIterator $nonUniqueIteratorInstanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $nonUniqueIteratorInstanceName = \Magento\Framework\DB\Query\BatchRangeIterator::class,
        $uniqueIteratorInstanceName = \Magento\Framework\DB\Query\BatchIterator::class
    ) {
        $this->objectManager = $objectManager;
        $this->uniqueIteratorInstanceName = $uniqueIteratorInstanceName;
        $this->nonUniqueIteratorInstanceName = $nonUniqueIteratorInstanceName;
    }

    /**
     * Create iterator instance with specified parameters
     *
     * Depending on the chosen strategy specified in $data['batchStrategy'] for selects,
     * create an instance of iterator.
     * By default will be created \Magento\Framework\DB\Query\BatchIterator class.
     * This iterator provide interface for accessing sub-selects which was created from main select.
     *
     * If $data['batchStrategy'] == 'non_unqiue' value then we should to create BatchRangeIterator. This Iterator
     * allows to operate with rangeField, which has one-to-many relations with other fields and is not unique.
     * The main idea is to separate select to few parts in order to reduce the load of SQL server.
     *
     * @see \Magento\Framework\DB\Query\Generator
     * @param array $data
     * @return  \Iterator
     */
    public function create(array $data = [])
    {
        if (isset($data['batchStrategy']) && $data['batchStrategy'] == self::NON_UNIQUE_FIELD_ITERATOR) {
            return $this->objectManager->create($this->nonUniqueIteratorInstanceName, $data);
        }

        return $this->objectManager->create($this->uniqueIteratorInstanceName, $data);
    }
}
