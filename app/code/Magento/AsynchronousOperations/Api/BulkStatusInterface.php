<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api;

/**
 * Interface BulkStatusInterface
 * Bulk summary data with list of operations items short data.
 *
 * @api
 */
interface BulkStatusInterface extends \Magento\Framework\Bulk\BulkStatusInterface
{
    /**
     * Get Bulk summary data with list of operations items full data.
     *
     * @param string $bulkUuid
     * @return \Magento\AsynchronousOperations\Api\Data\DetailedBulkStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBulkDetailedStatus($bulkUuid);

    /**
     * Get Bulk summary data with list of operations items short data.
     *
     * @param string $bulkUuid
     * @return \Magento\AsynchronousOperations\Api\Data\BulkStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBulkShortStatus($bulkUuid);
}
