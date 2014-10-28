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
namespace Magento\Sales\Service\V1;

use Magento\Sales\Service\V1\Data\OrderAddress;
use Magento\Sales\Service\V1\Data\OrderStatusHistory;

interface OrderWriteInterface
{
    /**
     * @param \Magento\Sales\Service\V1\Data\OrderAddress $orderAddress
     * @return bool
     */
    public function addressUpdate(OrderAddress $orderAddress);

    /**
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function cancel($id);

    /**
     * @param int $id
     * @return bool
     */
    public function email($id);

    /**
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hold($id);

    /**
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function unHold($id);

    /**
     * @param int $id
     * @param \Magento\Sales\Service\V1\Data\OrderStatusHistory $statusHistory
     * @return bool
     */
    public function statusHistoryAdd($id, OrderStatusHistory $statusHistory);

    /**
     * Create an order
     *
     * @param \Magento\Sales\Service\V1\Data\Order $orderDataObject
     * @return bool
     * @throws \Exception
     */
    public function create(\Magento\Sales\Service\V1\Data\Order $orderDataObject);
}
