<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api;

use Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterface;
use Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterface;
use Magento\Framework\Bulk\BulkStatusInterface as AsynchronousBulkStatusInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface BulkStatusInterface.
 *
 * Bulk summary data with list of operations items short data.
 *
 * @api
 */
interface BulkStatusInterface extends AsynchronousBulkStatusInterface
{
    /**
     * Get Bulk summary data with list of operations items full data.
     *
     * @param string $bulkUuid
     * @return DetailedBulkOperationsStatusInterface
     * @throws NoSuchEntityException
     */
    public function getBulkDetailedStatus(string $bulkUuid): DetailedBulkOperationsStatusInterface;

    /**
     * Get Bulk summary data with list of operations items short data.
     *
     * @param string $bulkUuid
     * @return BulkOperationsStatusInterface
     * @throws NoSuchEntityException
     */
    public function getBulkShortStatus(string $bulkUuid): BulkOperationsStatusInterface;
}
