<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 * Checks if is possible to submit guarantee request for order.
 */
class SubmitEligible
{
    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * Eligible count of days from the order date to submit a case for Guarantee.
     *
     * @var int
     */
    private static $guarantyEligibleDays = 7;

    /**
     * @param CaseManagement $caseManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param DateTimeFactory $dateTimeFactory
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
     * Checks if Guarantee submit is applicable for order and case.
     *
     * @param int $orderId
     * @return bool
     */
    public function check($orderId)
    {
        $case = $this->caseManagement->getByOrderId($orderId);
        if (null === $case || $case->isGuaranteeEligible() === false) {
            return false;
        }

        $order = $this->getOrder($orderId);
        if (null === $order || $this->checkOrder($order) === false) {
            return false;
        }

        return true;
    }

    /**
     * Checks if Guarantee submit is applicable for order.
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function checkOrder(OrderInterface $order)
    {
        if (in_array($order->getState(), [Order::STATE_CANCELED, Order::STATE_CLOSED])) {
            return false;
        }

        $orderCreateDate = $this->dateTimeFactory->create($order->getCreatedAt(), new \DateTimeZone('UTC'));
        $currentDate = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
        if ($orderCreateDate->diff($currentDate)->days >= static::$guarantyEligibleDays) {
            return false;
        }

        return true;
    }

    /**
     * Returns order by id
     *
     * @param int $orderId
     * @return OrderInterface|null
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
