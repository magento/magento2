<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event;
use Magento\Sales\Api\Data\OrderInterface;

use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Api\CaseCreationServiceInterface;

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
     * PlaceOrder constructor.
     *
     * @param Config $signifydIntegrationConfig
     * @param CaseCreationServiceInterface $caseCreationService
     */
    public function __construct(
        Config $signifydIntegrationConfig,
        CaseCreationServiceInterface $caseCreationService
    ) {
        $this->signifydIntegrationConfig = $signifydIntegrationConfig;
        $this->caseCreationService = $caseCreationService;
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
            $orderId = $order->getEntityId();
            if (null !== $orderId) {
                $this->caseCreationService->createForOrder($orderId);
            }
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
