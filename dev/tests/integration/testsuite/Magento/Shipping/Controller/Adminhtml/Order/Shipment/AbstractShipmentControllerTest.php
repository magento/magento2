<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Abstract backend shipment test.
 */
class AbstractShipmentControllerTest extends AbstractBackendController
{
    /**
     * @var TransportBuilderMock
     */
    protected $transportBuilder;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var string
     */
    protected $resource = 'Magento_Sales::shipment';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->transportBuilder = $this->_objectManager->get(TransportBuilderMock::class);
        $this->orderRepository = $this->_objectManager->get(OrderRepository::class);
        $this->formKey = $this->_objectManager->get(FormKey::class);
    }

    /**
     * @param string $incrementalId
     * @return OrderInterface|null
     */
    protected function getOrder(string $incrementalId)
    {
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->_objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter(OrderInterface::INCREMENT_ID, $incrementalId)
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        /** @var OrderInterface|null $order */
        $order = reset($orders);

        return $order;
    }

    /**
     * @param OrderInterface $order
     * @return ShipmentInterface
     */
    protected function getShipment(OrderInterface $order): ShipmentInterface
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipmentCollection */
        $shipmentCollection = $this->_objectManager->create(
            \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory::class
        )->create();

        /** @var ShipmentInterface $shipment */
        $shipment = $shipmentCollection
            ->setOrderFilter($order)
            ->setPageSize(1)
            ->getFirstItem();

        return $shipment;
    }
}
