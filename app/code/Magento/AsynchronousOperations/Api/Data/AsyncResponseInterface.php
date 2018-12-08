<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * Interface AsyncResponseInterface
 * Temporary data object to give response from webapi async router
 *
 * @api
 */
interface AsyncResponseInterface
{
    const BULK_UUID = 'bulk_uuid';
    const REQUEST_ITEMS = 'request_items';
    const ERRORS = 'errors';

    /**
     * Gets the bulk uuid.
     *
     * @return string Bulk Uuid.
     */
    public function getBulkUuid();

    /**
     * Sets the bulk uuid.
     *
     * @param string $bulkUuid
     * @return $this
     */
    public function setBulkUuid($bulkUuid);

    /**
     * Gets the list of request items with status data.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\ItemStatusInterface[]
     */
    public function getRequestItems();

    /**
     * Sets the list of request items with status data.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\ItemStatusInterface[] $requestItems
     * @return $this
     */
    public function setRequestItems($requestItems);

    /**
     * @param bool $isErrors
     * @return $this
     */
    public function setErrors($isErrors = false);

    /**
     * Is there errors during processing bulk
     *
     * @return boolean
     */
    public function isErrors();

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\AsyncResponseExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\AsyncResponseExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\AsynchronousOperations\Api\Data\AsyncResponseExtensionInterface $extensionAttributes
    );
}
