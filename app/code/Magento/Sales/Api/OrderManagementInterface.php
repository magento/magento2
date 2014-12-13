<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api;

/**
 * Interface OrderManagementInterface
 */
interface OrderManagementInterface
{
    /**
     * Order cancel
     *
     * @param int $id
     * @return bool
     */
    public function cancel($id);

    /**
     * Returns list of comments attached to order
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderStatusHistorySearchResultInterface
     */
    public function getCommentsList($id);

    /**
     * Add comment to order
     *
     * @param int $id
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory
     * @return bool
     */
    public function addComment($id, \Magento\Sales\Api\Data\OrderStatusHistoryInterface $statusHistory);

    /**
     * Notify user
     *
     * @param int $id
     * @return bool
     */
    public function notify($id);

    /**
     * Returns order status
     *
     * @param int $id
     * @return string
     */
    public function getStatus($id);

    /**
     * Order hold
     *
     * @param int $id
     * @return bool
     */
    public function hold($id);

    /**
     * Order un hold
     *
     * @param int $id
     * @return bool
     */
    public function unHold($id);
}
