<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Guarantee;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Signifyd\Model\CaseManagement;

/**
 * Checks if is possible to create Guarantee for order.
 * @since 2.2.0
 */
class CreateGuaranteeAbility
{
    /**
     * @var CaseManagement
     * @since 2.2.0
     */
    private $caseManagement;

    /**
     * @var OrderRepositoryInterface
     * @since 2.2.0
     */
    private $orderRepository;

    /**
     * @var DateTimeFactory
     * @since 2.2.0
     */
    private $dateTimeFactory;

    /**
     * Eligible count of days from the order creation date to submit a case for Guarantee.
     *
     * @var int
     * @since 2.2.0
     */
    private static $guarantyEligibleDays = 7;

    /**
     * @param CaseManagement $caseManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param DateTimeFactory $dateTimeFactory
     * @since 2.2.0
     */
    public function __construct(
        CaseManagement $caseManagement,
        OrderRepositoryInterface $orderRepository,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->caseManagement = $caseManagement;
        $this->orderRepository = $orderRepository;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Checks if it is possible to create Guarantee for order and case.
     *
     * @param int $orderId
     * @return bool
     * @since 2.2.0
     */
    public function isAvailable($orderId)
    {
        $case = $this->caseManagement->getByOrderId($orderId);
        if (null === $case) {
            return false;
        }

        if ($case->isGuaranteeEligible() === false) {
            return false;
        }

        $order = $this->getOrder($orderId);
        if (null === $order) {
            return false;
        }

        if (in_array($order->getState(), [Order::STATE_CANCELED, Order::STATE_CLOSED])) {
            return false;
        }

        if ($this->isOrderOlderThen(static::$guarantyEligibleDays, $order)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if Guarantee submit is applicable for order.
     *
     * @param OrderInterface $order
     * @param int $days number of days from the order creation date to submit a case for Guarantee.
     * @return bool
     * @since 2.2.0
     */
    private function isOrderOlderThen($days, OrderInterface $order)
    {
        $orderCreateDate = $this->dateTimeFactory->create($order->getCreatedAt(), new \DateTimeZone('UTC'));
        $currentDate = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));

        return $orderCreateDate->diff($currentDate)->days >= $days;
    }

    /**
     * Returns order by id
     *
     * @param int $orderId
     * @return OrderInterface|null
     * @since 2.2.0
     */
    private function getOrder($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (InputException $e) {
            return null;
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $order;
    }
}
