<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api;

/**
 * @api
 * @since 100.3.0
 */
interface BulkRepositoryInterface
{

    /**
     * @param string $bulkUuid
     * @return \Magento\AsynchronousOperations\Api\Data\BulkStatus\DetailedInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 100.3.0
     */
    public function getBulkDetailedStatus($bulkUuid);

    /**
     * @param string $bulkUuid
     * @return \Magento\AsynchronousOperations\Api\Data\BulkStatus\ShortInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 100.3.0
     */
    public function getBulkShortStatus($bulkUuid);
}
