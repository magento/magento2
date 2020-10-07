<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * Interface BulkStatusInterface
 *
 * Bulk summary data with list of operations items full data.
 *
 * @api
 * @since 100.2.3
 */
interface DetailedBulkOperationsStatusInterface extends BulkSummaryInterface
{

    const OPERATIONS_LIST = 'operations_list';

    /**
     * Retrieve operations list.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationInterface[]
     * @since 100.2.3
     */
    public function getOperationsList();

    /**
     * Set operations list.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface[] $operationStatusList
     * @return $this
     * @since 100.2.3
     */
    public function setOperationsList($operationStatusList);
}
