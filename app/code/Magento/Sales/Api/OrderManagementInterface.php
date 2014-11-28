<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
