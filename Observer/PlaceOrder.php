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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Signifyd\Api\CaseCreationServiceInterface;
use Magento\Signifyd\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Observer should be triggered when new order is created and placed.
 * If Signifyd integration enabled in configuration then new case will be created.
 * @since 2.2.0
 */
class PlaceOrder implements ObserverInterface
{
    /**
     * @var Config
     * @since 2.2.0
     */
    private $signifydIntegrationConfig;

    /**
     * @var CaseCreationServiceInterface
     * @since 2.2.0
     */
    private $caseCreationService;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @param Config $signifydIntegrationConfig
     * @param CaseCreationServiceInterface $caseCreationService
     * @param LoggerInterface $logger
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function execute(Observer $observer)
    {
        if (!$this->signifydIntegrationConfig->isActive()) {
            return;
        }

        $orders = $this->extractOrders(
            $observer->getEvent()
        );

        if (null === $orders) {
            return;
        }

        foreach ($orders as $order) {
            $this->createCaseForOrder($order);
        }
    }

    /**
     * Creates Signifyd case for single order with online payment method.
     *
     * @param OrderInterface $order
     * @return void
     * @since 2.2.0
     */
    private function createCaseForOrder($order)
    {
        $orderId = $order->getEntityId();
        if (null === $orderId || $order->getPayment()->getMethodInstance()->isOffline()) {
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
     * @since 2.2.0
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
