<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Invoice management interface.
 *
 * An invoice is a record of the receipt of payment for an order.
 * @api
 * @since 2.0.0
 */
interface InvoiceManagementInterface
{
    /**
     * Sets invoice capture.
     *
     * @param int $id
     * @return string
     * @since 2.0.0
     */
    public function setCapture($id);

    /**
     * Lists comments for a specified invoice.
     *
     * @param int $id The invoice ID.
     * @return \Magento\Sales\Api\Data\InvoiceCommentSearchResultInterface Invoice comment search result interface.
     * @since 2.0.0
     */
    public function getCommentsList($id);

    /**
     * Emails a user a specified invoice.
     *
     * @param int $id The invoice ID.
     * @return bool
     * @since 2.0.0
     */
    public function notify($id);

    /**
     * Voids a specified invoice.
     *
     * @param int $id The invoice ID.
     * @return bool
     * @since 2.0.0
     */
    public function setVoid($id);
}
