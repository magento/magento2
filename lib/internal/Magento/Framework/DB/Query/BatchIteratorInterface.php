<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Query;

/**
 * Batch Iterator interface
 * @since 2.2.0
 */
interface BatchIteratorInterface extends \Iterator
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
     * Return the current element
     *
     * If we don't have sub-select we should create and remember it.
     *
     * @return \Magento\Framework\DB\Select
     * @since 2.2.0
     */
    public function current();

    /**
     * Return the key of the current element
     *
     * Сan return the number of the current sub-select in the iteration.
     *
     * @return int
     * @since 2.2.0
     */
    public function key();

    /**
     * Move forward to next sub-select
     *
     * Retrieve the next sub-select and move cursor to the next element.
     * Checks that the count of elements more than the sum of limit and offset.
     *
     * @return \Magento\Framework\DB\Select
     * @since 2.2.0
     */
    public function next();

    /**
     * Rewind the BatchRangeIterator to the first element.
     *
     * Allows to start iteration from the beginning.
     *
     * @return void
     * @since 2.2.0
     */
    public function rewind();

    /**
     * Checks if current position is valid
     *
     * @return bool
     * @since 2.2.0
     */
    public function valid();
}
