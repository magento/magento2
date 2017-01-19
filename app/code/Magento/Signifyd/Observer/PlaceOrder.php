<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event;
use Magento\Sales\Api\Data\OrderInterface;

use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Api\CaseCreationServiceInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Place Order observer.
 *
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
     * PlaceOrder constructor.
     *
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
     * Creates signifyd case for single order
     *
     * @param OrderInterface $order
     * @return void
     */
    private function createCaseForOrder($order)
    {
        $orderId = $order->getEntityId();
        if (null === $orderId) {
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
