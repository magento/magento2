<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api;

/**
 * Interface BulkStatusInterface.
 *
 * Bulk summary data with list of operations items short data.
 *
 * @api
 * @since 100.2.3
 */
interface BulkStatusInterface extends \Magento\Framework\Bulk\BulkStatusInterface
{
    /**
     * Get Bulk summary data with list of operations items full data.
     *
     * @param string $bulkUuid
     * @return \Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 100.2.3
     */
    public function getBulkDetailedStatus($bulkUuid);

    /**
     * Get Bulk summary data with list of operations items short data.
     *
     * @param string $bulkUuid
     * @return \Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 100.2.3
     */
    public function getBulkShortStatus($bulkUuid);
}
