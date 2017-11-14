<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

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
     * @param OrderInjectable $order
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param $resultTrackingData
     */
    public function processAssert(
        BrowserInterface $browser,
        OrderInjectable $order,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        $resultTrackingData
    ) {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()
            ->searchAndOpen(['id' => $order->getId()]);

        /** @var Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()
            ->openTab('info')
            ->getTab('info');

        $mainWindow = $browser->getCurrentWindow();
        $shippingInfoBlock = $infoTab->getShippingInfoBlock();
        $shippingInfoBlock->openTrackingPopup();

        $browser->selectWindow();

        $selector = '.close';
        $browser->waitUntil(function () use ($browser, $selector) {
            $element = $browser->find($selector);
            return $element->isVisible() ? true : null;
        });

        $body = $browser->find($this->mainContainer)->getText();
        foreach ($resultTrackingData as $value) {
            \PHPUnit_Framework_Assert::assertContains(
                $value,
                $body,
                'The "' . $value . '" is not present in Shipping Tracking popup.'
            );
        }
        $popupWindow = $browser->getCurrentWindow();
        $browser->selectWindow($mainWindow);
        $browser->closeWindow($popupWindow);
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Shipment tracking data is present in the popup window.';
    }
}
