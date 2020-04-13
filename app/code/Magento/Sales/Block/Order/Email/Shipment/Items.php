<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Order\Email\Shipment;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Sales Order Email Shipment items
 *
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @param Context $context
     * @param array $data
     * @param OrderRepositoryInterface|null $orderRepository
     * @param ShipmentRepositoryInterface|null $creditmemoRepository
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?OrderRepositoryInterface $orderRepository = null,
        ?ShipmentRepositoryInterface $creditmemoRepository = null
    ) {
        $this->orderRepository =
            $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);
        $this->shipmentRepository =
            $creditmemoRepository ?: ObjectManager::getInstance()->get(ShipmentRepositoryInterface::class);

        parent::__construct($context, $data);
    }

    /**
     * Prepare item before output
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $renderer
     * @return void
     */
    protected function _prepareItem(\Magento\Framework\View\Element\AbstractBlock $renderer)
    {
        $renderer->getItem()->setOrder($this->getOrder());
        $renderer->getItem()->setSource($this->getShipment());
    }

    /**
     * Returns order.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So order is loaded by order_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        $order = $this->getData('order');
        if ($order !== null) {
            return $order;
        }

        $orderId = (int)$this->getData('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $this->setData('order', $order);
        }

        return $this->getData('order');
    }

    /**
     * Returns shipment.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So shipment is loaded by shipment_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return ShipmentInterface|null
     */
    public function getShipment()
    {
        $shipment = $this->getData('shipment');
        if ($shipment !== null) {
            return $shipment;
        }

        $shipmentId = (int)$this->getData('shipment_id');
        if ($shipmentId) {
            $shipment = $this->shipmentRepository->get($shipmentId);
            $this->setData('shipment', $shipment);
        }

        return $this->getData('shipment');
    }
}
