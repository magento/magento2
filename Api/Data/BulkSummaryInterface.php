<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * Interface BulkSummaryInterface
 * @api
 */
interface BulkSummaryInterface extends \Magento\Framework\Bulk\BulkSummaryInterface
{
    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\AsynchronousOperations\Api\Data\BulkSummaryExtensionInterface $extensionAttributes
    );
}
