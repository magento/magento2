<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Shipping\Test\Page\Adminhtml\OrderShipmentNew;
use Magento\Shipping\Test\Page\Adminhtml\OrderShipmentView;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create shipping from order on backend.
 */
class CreateShipmentStep implements TestStepInterface
{
    /**
     * Shipment column title.
     */
    const COLUMN_NAME = 'Shipment';

    /**
     * Orders Page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * New Order Shipment Page.
     *
     * @var OrderShipmentNew
     */
    protected $orderShipmentNew;

    /**
     * Order shipment view page.
     *
     * @var OrderShipmentView
     */
    protected $orderShipmentView;

    /**
     * OrderInjectable fixture.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * Invoice data.
     *
     * @var array|null
     */
    protected $data;

    /**
     * @construct
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderShipmentNew $orderShipmentNew
     * @param OrderShipmentView $orderShipmentView
     * @param OrderInjectable $order
     * @param array|null $data [optional]
     */
    public function __construct(
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderShipmentNew $orderShipmentNew,
        OrderShipmentView $orderShipmentView,
        OrderInjectable $order,
        $data = null
    ) {
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->orderShipmentNew = $orderShipmentNew;
        $this->orderShipmentView = $orderShipmentView;
        $this->order = $order;
        $this->data = $data;
    }

    /**
     * Create shipping for order on backend.
     *
     * @return array
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $this->salesOrderView->getPageActions()->ship();
        if (!empty($this->data)) {
            $this->orderShipmentNew->getFormBlock()->fillData($this->data, $this->order->getEntityId()['products']);
        }
        $this->orderShipmentNew->getFormBlock()->submit();

        return ['shipmentIds' => $this->getShipmentIds()];
    }

    /**
     * Get shipment id.
     *
     * @return array
     */
    public function getShipmentIds()
    {
        $this->salesOrderView->getOrderForm()->openTab('shipments');
        $this->salesOrderView->getOrderForm()->getTab('shipments')->getGridBlock()->resetFilter();
        $shipmentIds = $this->salesOrderView->getOrderForm()->getTab('shipments')->getGridBlock()->getAllIds();
        $incrementIds = [];
        foreach ($shipmentIds as $shipmentId) {
            $incrementIds[] = $this->salesOrderView->getOrderForm()
                ->getTab('shipments')
                ->getGridBlock()
                ->getColumnValue($shipmentId, self::COLUMN_NAME);
        }
        return $incrementIds;
    }
}
