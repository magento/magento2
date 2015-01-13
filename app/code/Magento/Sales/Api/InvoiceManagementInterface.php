<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Invoice management interface.
 *
 * An invoice is a record of the receipt of payment for an order.
 */
interface InvoiceManagementInterface
{
    /**
     * Sets invoice capture.
     *
     * @param int $id
     * @return string
     */
    public function setCapture($id);

    /**
     * Lists comments for a specified invoice.
     *
     * @param int $id The invoice ID.
     * @return \Magento\Sales\Api\Data\InvoiceCommentSearchResultInterface Invoice comment search result interface.
     */
    public function getCommentsList($id);

    /**
     * Emails a user a specified invoice.
     *
     * @param int $id The invoice ID.
     * @return bool
     */
    public function notify($id);

    /**
     * Voids a specified invoice.
     *
     * @param int $id The invoice ID.
     * @return bool
     */
    public function setVoid($id);
}
