<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Guarantee;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Model\CaseManagement;

/**
 * Checks if is possible to cancel Guarantee for order.
 * @since 2.2.0
 */
class CancelGuaranteeAbility
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
     * @param CaseManagement $caseManagement
     * @param OrderRepositoryInterface $orderRepository
     * @since 2.2.0
     */
    public function __construct(
        CaseManagement $caseManagement,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->caseManagement = $caseManagement;
        $this->orderRepository = $orderRepository;
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
        if ($case === null) {
            return false;
        }

        if (in_array($case->getGuaranteeDisposition(), [null, $case::GUARANTEE_CANCELED])) {
            return false;
        }

        $order = $this->getOrder($orderId);
        if (null === $order) {
            return false;
        }

        return true;
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
