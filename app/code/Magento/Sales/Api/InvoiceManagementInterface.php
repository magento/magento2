<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api;

/**
 * Interface InvoiceManagementInterface
 */
interface InvoiceManagementInterface
{
    /**
     * Set invoice capture
     *
     * @param int $id
     * @return string
     */
    public function setCapture($id);

    /**
     * Returns list of comments attached to invoice
     * @param int $id
     * @return \Magento\Sales\Api\Data\InvoiceCommentSearchResultInterface
     */
    public function getCommentsList($id);

    /**
     * Notify user
     *
     * @param int $id
     * @return bool
     */
    public function notify($id);

    /**
     * Set invoice void
     *
     * @param int $id
     * @return bool
     */
    public function setVoid($id);
}
