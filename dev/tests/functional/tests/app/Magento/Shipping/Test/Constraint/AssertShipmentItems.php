<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Sales\Test\Constraint\AbstractAssertItems;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Shipping\Test\Page\Adminhtml\SalesShipmentView;
use Magento\Shipping\Test\Page\Adminhtml\ShipmentIndex;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\System\Event\EventManagerInterface;

/**
 * Assert shipment items on shipment view page.
 */
class AssertShipmentItems extends AbstractAssertItems
{
    /**
     * Shipment index page.
     *
     * @var ShipmentIndex
     */
    protected $shipmentPage;

    /**
     * @constructor
     * @param ObjectManager $objectManager
     * @param EventManagerInterface $eventManager
     * @param ShipmentIndex $shipmentIndex
     */
    public function __construct(
        ObjectManager $objectManager,
        EventManagerInterface $eventManager,
        ShipmentIndex $shipmentIndex
    ) {
        parent::__construct($objectManager, $eventManager);
        $this->shipmentPage = $shipmentIndex;
    }

    /**
     * Assert shipped products are represented on shipment view page.
     *
     * @param SalesShipmentView $orderShipmentView
     * @param OrderInjectable $order
     * @param array $ids
     * @param array|null $data [optional]
     * @return void
     */
    public function processAssert(
        SalesShipmentView $orderShipmentView,
        OrderInjectable $order,
        array $ids,
        array $data = null
    ) {
        $this->shipmentPage->open();
        $this->assert($order, $ids, $orderShipmentView, $data);
    }

    /**
     * Process assert.
     *
     * @param OrderInjectable $order
     * @param array $ids
     * @param SalesShipmentView $salesShipmentView
     * @param array|null $data [optional]
     * @return void
     */
    protected function assert(
        OrderInjectable $order,
        array $ids,
        SalesShipmentView $salesShipmentView,
        array $data = null
    ) {
        $orderId = $order->getId();
        $productsData = $this->prepareOrderProducts($order, $data['items_data']);
        foreach ($ids['shipmentIds'] as $shipmentId) {
            $filter = [
                'order_id' => $orderId,
                'id' => $shipmentId,
            ];
            $this->shipmentPage->getShipmentsGrid()->searchAndOpen($filter);
            $itemsData = $this->preparePageItems($salesShipmentView->getItemsBlock()->getData());
            $error = $this->verifyData($productsData, $itemsData);
            \PHPUnit_Framework_Assert::assertEmpty($error, $error);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'All shipment products are present in shipment page.';
    }
}
