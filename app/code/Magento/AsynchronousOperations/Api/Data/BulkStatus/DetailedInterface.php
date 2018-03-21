<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api\Data\BulkStatus;

use Magento\AsynchronousOperations\Api\Data\OperationDetailsInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\OperationStatus\DetailedListInterface;

/**
 * Interface BulkStatusInterface
 *
 * @api
 * @since 100.3.0
 */
interface DetailedInterface extends BulkSummaryInterface
{

    const OPERATIONS_LIST = 'operations_list';
    const OPERATIONS_COUNTER = 'operations_counter';

    /**
     * Retrieve operations list.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationStatus\DetailedInterface[]
     * @since 100.3.0
     */
    public function getOperationsList();

    /**
     * Set operations list.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationStatus\DetailedInterface[] $operationStatusList
     * @return $this
     * @since 100.3.0
     */
    public function setOperationsList($operationStatusList);

    /**
     * Retrieve operations counter object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationDetailsInterface|null
     * @since 100.3.0
     */
    public function getOperationsCounter();

    /**
     * Set operations counter object.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationDetailsInterface $operationDetails
     * @return $this
     * @since 100.3.0
     */
    public function setOperationsCounter(OperationDetailsInterface $operationDetails
    );
}
