<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Api\Data;

/**
 * Interface AsyncResponseInterface
 * Temporary data object to give response from webapi async router
 *
 * @api
 * @since 100.3.0
 */
interface AsyncResponseInterface
{
    const BULK_UUID = 'bulk_uuid';
    const REQUEST_ITEMS = 'request_items';

    /**
     * Gets the bulk uuid.
     *
     * @return string Bulk Uuid.
     * @since 100.3.0
     */
    public function getBulkUuid();

    /**
     * Sets the bulk uuid.
     *
     * @param string $bulkUuid
     * @return $this
     * @since 100.3.0
     */
    public function setBulkUuid($bulkUuid);

    /**
     * Gets the list of request items with status data.
     *
     * @return \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterface
     * @since 100.3.0
     */
    public function getRequestItems();

    /**
     * Sets the list of request items with status data.
     *
     * @param \Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterface $requestItems
     * @return $this
     * @since 100.3.0
     */
    public function setRequestItems(\Magento\WebapiAsync\Api\Data\AsyncResponse\ItemsListInterface $requestItems);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\WebapiAsync\Api\Data\AsyncResponseExtensionInterface|null
     * @since 100.3.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\WebapiAsync\Api\Data\AsyncResponseExtensionInterface $extensionAttributes
     * @return $this
     * @since 100.3.0
     */
    public function setExtensionAttributes(
        \Magento\WebapiAsync\Api\Data\AsyncResponseExtensionInterface $extensionAttributes
    );
}
