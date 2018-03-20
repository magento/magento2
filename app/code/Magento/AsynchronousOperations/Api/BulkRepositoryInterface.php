<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api;

/**
 * @api
 * @since 100.3.0
 */
interface BulkRepositoryInterface extends \Magento\Framework\Bulk\BulkStatusInterface
{

    /**
     * @param string $bulkUuid
     * @return \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 100.3.0
     */
    public function getBulkDetails($bulkUuid);
}
