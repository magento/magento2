<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Order\Invoice\Total;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    public function testCollect()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Model\Order\Invoice\Total\Tax $model */
        $model = $objectManager->getObject('Magento\Sales\Model\Order\Invoice\Total\Tax');

        $collection = $objectManager
            ->getCollectionMock('Magento\Sales\Model\Resource\Order\Invoice\Collection', array());

        $order = $this->getMock(
            '\Magento\Sales\Model\Order',
            array(
                'getInvoiceCollection',
                'getHiddenTaxAmount',
                'getBaseHiddenTaxAmount',
                '__wakeup'
            ),
            array(),
            '',
            false
        );
        $order->expects($this->atLeastOnce())->method('getInvoiceCollection')->will($this->returnValue($collection));
        $order->expects($this->atLeastOnce())->method('getHiddenTaxAmount')->will($this->returnValue(10));
        $order->expects($this->atLeastOnce())->method('getBaseHiddenTaxAmount')->will($this->returnValue(10));

        $invoiceItems[] = $this->getInvoiceItem(0, 10);

        $invoice = $this->getMock(
            '\Magento\Sales\Model\Order\Invoice',
            array(
                'getAllItems',
                'getOrder',
                'getGrandTotal',
                'setGrandTotal',
                '__wakeup'
            ),
            array(),
            '',
            false
        );
        $invoice->expects($this->atLeastOnce())->method('getAllItems')->will($this->returnValue($invoiceItems));
        $invoice->expects($this->atLeastOnce())->method('getOrder')->will($this->returnValue($order));
        $invoice->expects($this->atLeastOnce())->method('getGrandTotal')->will($this->returnValue(0));
        $invoice
            ->expects($this->atLeastOnce())
            ->method('setGrandTotal')
            ->with($this->equalTo(10))
            ->will($this->returnSelf());

        $model->collect($invoice);
    }

    /**
     * @param $taxAmount
     * @param $hiddenTaxAmount
     * @return \Magento\Sales\Model\Order\Invoice\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getInvoiceItem($taxAmount, $hiddenTaxAmount)
    {
        $orderItem = $this->getMock(
            '\Magento\Sales\Model\Order\Item',
            array(
                'getQtyOrdered',
                'getTaxAmount',
                'getBaseTaxAmount',
                'getHiddenTaxAmount',
                'getBaseHiddenTaxAmount',
                '__wakeup'
            ),
            array(),
            '',
            false
        );
        $orderItem->expects($this->atLeastOnce())->method('getQtyOrdered')->will($this->returnValue(1));
        $orderItem->expects($this->atLeastOnce())->method('getTaxAmount')->will($this->returnValue($taxAmount));
        $orderItem->expects($this->atLeastOnce())->method('getBaseTaxAmount')->will($this->returnValue($taxAmount));
        $orderItem
            ->expects($this->atLeastOnce())
            ->method('getHiddenTaxAmount')
            ->will($this->returnValue($hiddenTaxAmount));
        $orderItem
            ->expects($this->atLeastOnce())
            ->method('getBaseHiddenTaxAmount')
            ->will($this->returnValue($hiddenTaxAmount));

        $invoiceItem = $this->getMock(
            '\Magento\Sales\Model\Order\Invoice\Item',
            array(
                'getOrderItem',
                'isLast',
                'setTaxAmount',
                'setBaseTaxAmount',
                'setHiddenTaxAmount',
                'setBaseHiddenTaxAmount',
                '__wakeup'
            ),
            array(),
            '',
            false
        );
        $invoiceItem->expects($this->atLeastOnce())->method('getOrderItem')->will($this->returnValue($orderItem));
        $invoiceItem->expects($this->atLeastOnce())->method('isLast')->will($this->returnValue(true));

        $invoiceItem->expects($this->once())->method('setTaxAmount')->with($taxAmount)->will($this->returnSelf());
        $invoiceItem->expects($this->once())->method('setBaseTaxAmount')->with($taxAmount)->will($this->returnSelf());
        $invoiceItem
            ->expects($this->once())
            ->method('setHiddenTaxAmount')
            ->with($hiddenTaxAmount)
            ->will($this->returnSelf());
        $invoiceItem
            ->expects($this->once())
            ->method('setBaseHiddenTaxAmount')
            ->with($hiddenTaxAmount)
            ->will($this->returnSelf());
        return $invoiceItem;
    }
}
