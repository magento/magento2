<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Updates order state.
 * @since 2.2.0
 */
class OrderStateService
{
    /**
     * @var OrderFactory
     * @since 2.2.0
     */
    private $orderFactory;

    /**
     * @var OrderManagementInterface
     * @since 2.2.0
     */
    private $orderManagement;

    /**
     * @var CommentsHistoryUpdater
     * @since 2.2.0
     */
    private $commentsHistoryUpdater;

    /**
     * @param OrderFactory $orderFactory
     * @param OrderManagementInterface $orderManagement
     * @param CommentsHistoryUpdater $commentsHistoryUpdater
     * @since 2.2.0
     */
    public function __construct(
        OrderFactory $orderFactory,
        OrderManagementInterface $orderManagement,
        CommentsHistoryUpdater $commentsHistoryUpdater
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderManagement = $orderManagement;
        $this->commentsHistoryUpdater = $commentsHistoryUpdater;
    }

    /**
     * Updates order state depending on case guarantee disposition status.
     *
     * @param CaseInterface $case
     * @return void
     * @since 2.2.0
     */
    public function updateByCase(CaseInterface $case)
    {
        $orderId = $case->getOrderId();

        switch ($case->getGuaranteeDisposition()) {
            case CaseInterface::GUARANTEE_APPROVED:
                $this->unHold($orderId);
                break;
            case CaseInterface::GUARANTEE_DECLINED:
                $this->hold($orderId);
                break;
            case CaseInterface::GUARANTEE_PENDING:
                if ($this->hold($orderId)) {
                    $this->commentsHistoryUpdater->addComment(
                        $case,
                        __('Awaiting the Signifyd guarantee disposition.'),
                        Order::STATE_HOLDED
                    );
                }
                break;
        }
    }

    /**
     * Tries to unhold the order.
     *
     * @param int $orderId
     * @return bool
     * @since 2.2.0
     */
    private function unHold($orderId)
    {
        $order = $this->getOrder($orderId);
        if ($order->canUnhold()) {
            return $this->orderManagement->unHold($orderId);
        }

        return false;
    }

    /**
     * Tries to hold the order.
     *
     * @param int $orderId
     * @return bool
     * @since 2.2.0
     */
    private function hold($orderId)
    {
        $order = $this->getOrder($orderId);
        if ($order->canHold()) {
            return $this->orderManagement->hold($orderId);
        }

        return false;
    }

    /**
     * Returns the order.
     *
     * @param int $orderId
     * @return Order
     * @since 2.2.0
     */
    private function getOrder($orderId)
    {
        return $this->orderFactory->create()->load($orderId);
    }
}
