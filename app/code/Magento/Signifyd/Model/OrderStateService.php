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
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class OrderStateService
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var CommentsHistoryUpdater
     */
    private $commentsHistoryUpdater;

    /**
     * @param OrderFactory $orderFactory
     * @param OrderManagementInterface $orderManagement
     * @param CommentsHistoryUpdater $commentsHistoryUpdater
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
     */
    private function getOrder($orderId)
    {
        return $this->orderFactory->create()->load($orderId);
    }
}
