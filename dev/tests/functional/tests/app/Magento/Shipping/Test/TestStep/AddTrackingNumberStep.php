<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Shipping\Test\Page\Adminhtml\SalesShipmentView;
use Magento\Shipping\Test\Page\Adminhtml\ShipmentIndex;

/**
 * Step to create shipping tracking number for created order shipment.
 */
class AddTrackingNumberStep implements TestStepInterface
{
    /**
     * @var ShipmentIndex
     */
    private $shipmentIndex;

    /**
     * @var SalesShipmentView
     */
    private $salesShipmentView;

    /**
     * @var array
     */
    private $shipmentIds;

    /**
     * @var array
     */
    private $trackingData;

    /**
     * @param ShipmentIndex $shipmentIndex
     * @param SalesShipmentView $salesShipmentView
     * @param array $shipmentIds
     * @param array $trackingData
     */
    public function __construct(
        ShipmentIndex $shipmentIndex,
        SalesShipmentView $salesShipmentView,
        array $shipmentIds,
        array $trackingData
    ) {
        $this->shipmentIndex = $shipmentIndex;
        $this->salesShipmentView = $salesShipmentView;
        $this->shipmentIds = $shipmentIds;
        $this->trackingData = $trackingData;
    }

    /**
     * Creates shipping tracking number.
     *
     * @return void
     */
    public function run()
    {
        $this->shipmentIndex->open();
        $this->shipmentIndex->getShipmentsGrid()
            ->searchAndOpen(['id' => array_pop($this->shipmentIds)]);

        $trackingInfoTable = $this->salesShipmentView->getTrackingInfoBlock();
        $trackingInfoTable->addTrackingNumber($this->trackingData);
    }
}
