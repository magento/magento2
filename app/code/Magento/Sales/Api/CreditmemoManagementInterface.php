<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api;

/**
 * Interface CreditmemoAddCommentInterface
 */
interface CreditmemoManagementInterface
{
    /**
     * Cancel an existing creditimemo
     *
     * @param int $id
     * @return bool
     */
    public function cancel($id);

    /**
     * Returns list of comments attached to creditmemo
     * @param int $id
     * @return \Magento\Sales\Api\Data\CreditmemoCommentSearchResultInterface
     */
    public function getCommentsList($id);

    /**
     * Notify user
     *
     * @param int $id
     * @return bool
     */
    public function notify($id);
}
