<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Shipping\Test\Page\Adminhtml\SalesShipmentView;
use Magento\Shipping\Test\Page\Adminhtml\ShipmentIndex;

/**
 * Class contains asserts for tracking number popup window.
 */
class AssertTrackingDetailsIsPresent extends AbstractConstraint
{
    /**
     * Selector for a main container.
     *
     * @var string
     */
    private $mainContainer = '.main';

    /**
     * Processes the assertions for tracking number data in the popup window.
     *
     * @param BrowserInterface $browser
     * @param ShipmentIndex $shipmentIndex
     * @param SalesShipmentView $salesShipmentView
     * @param $shipmentIds
     * @param $resultTrackingData
     */
    public function processAssert(
        BrowserInterface $browser,
        ShipmentIndex $shipmentIndex,
        SalesShipmentView $salesShipmentView,
        $shipmentIds,
        $resultTrackingData
    ) {
        $shipmentIndex->open();
        $shipmentIndex->getShipmentsGrid()
            ->searchAndOpen(['id' => array_pop($shipmentIds)]);

        $mainWindow = $browser->getCurrentWindow();
        $trackingInfoTable = $salesShipmentView->getTrackingInfoBlock();
        $trackingInfoTable->openPopup($resultTrackingData['number']);
        $browser->selectWindow();

        $selector = '.close';
        $browser->waitUntil(function () use ($browser, $selector) {
            $element = $browser->find($selector);
            return $element->isVisible() ? true : null;
        });

        $body = $browser->find($this->mainContainer)->getText();
        foreach ($resultTrackingData as $value) {
            \PHPUnit_Framework_Assert::assertContains($value, $body);
        }

        $browser->find($selector)->click();
        $browser->selectWindow($mainWindow);
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Shipment tracking data is present in the popup window';
    }
}
