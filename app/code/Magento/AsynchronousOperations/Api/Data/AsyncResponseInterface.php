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
 * @since 100.2.3
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
     * @since 100.2.3
     */
    public function getBulkUuid();

    /**
     * Sets the bulk uuid.
     *
     * @param string $bulkUuid
     * @return $this
     * @since 100.2.3
     */
    public function setBulkUuid($bulkUuid);

    /**
     * Gets the list of request items with status data.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\ItemStatusInterface[]
     * @since 100.2.3
     */
    public function getRequestItems();

    /**
     * Sets the list of request items with status data.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\ItemStatusInterface[] $requestItems
     * @return $this
     * @since 100.2.3
     */
    public function setRequestItems($requestItems);

    /**
     * Sets errors that occured during processing bulk
     *
     * @param bool $isErrors
     * @return $this
     * @since 100.2.3
     */
    public function setErrors($isErrors = false);

    /**
     * Is there errors during processing bulk
     *
     * @return boolean
     * @since 100.2.3
     */
    public function isErrors();

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\AsyncResponseExtensionInterface|null
     * @since 100.2.3
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\AsyncResponseExtensionInterface $extensionAttributes
     * @return $this
     * @since 100.2.3
     */
    public function setExtensionAttributes(
        \Magento\AsynchronousOperations\Api\Data\AsyncResponseExtensionInterface $extensionAttributes
    );
}
