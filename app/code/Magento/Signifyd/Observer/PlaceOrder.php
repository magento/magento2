<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
<<<<<<< HEAD
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
=======
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Api\Data\OrderInterface;
>>>>>>> upstream/2.2-develop
use Magento\Signifyd\Api\CaseCreationServiceInterface;
use Magento\Signifyd\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Observer should be triggered when new order is created and placed.
 * If Signifyd integration enabled in configuration then new case will be created.
 */
class PlaceOrder implements ObserverInterface
{
    /**
     * @var Config
     */
    private $signifydIntegrationConfig;

    /**
     * @var CaseCreationServiceInterface
     */
    private $caseCreationService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $signifydIntegrationConfig
     * @param CaseCreationServiceInterface $caseCreationService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $signifydIntegrationConfig,
        CaseCreationServiceInterface $caseCreationService,
        LoggerInterface $logger
    ) {
        $this->signifydIntegrationConfig = $signifydIntegrationConfig;
        $this->caseCreationService = $caseCreationService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
<<<<<<< HEAD
=======
     * @throws NotFoundException
>>>>>>> upstream/2.2-develop
     */
    public function execute(Observer $observer)
    {
        $orders = $this->extractOrders(
            $observer->getEvent()
        );

        if (null === $orders) {
            return;
        }

        foreach ($orders as $order) {
            $storeId = $order->getStoreId();
            if ($this->signifydIntegrationConfig->isActive($storeId)) {
                $this->createCaseForOrder($order);
            }
        }
    }

    /**
     * Creates Signifyd case for single order with online payment method.
     *
     * @param OrderInterface $order
     * @return void
<<<<<<< HEAD
=======
     * @throws NotFoundException
>>>>>>> upstream/2.2-develop
     */
    private function createCaseForOrder($order)
    {
        $orderId = $order->getEntityId();
<<<<<<< HEAD
        if (null === $orderId
            || $order->getPayment()->getMethodInstance()->isOffline()
            || $order->getState() === Order::STATE_PENDING_PAYMENT) {
=======
        if (null === $orderId || $order->getPayment()->getMethodInstance()->isOffline()) {
>>>>>>> upstream/2.2-develop
            return;
        }

        try {
            $this->caseCreationService->createForOrder($orderId);
        } catch (AlreadyExistsException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Returns Orders entity list from Event data container
     *
     * @param Event $event
     * @return OrderInterface[]|null
     */
    private function extractOrders(Event $event)
    {
        $order = $event->getData('order');
        if (null !== $order) {
            return [$order];
        }

        return $event->getData('orders');
    }
}
