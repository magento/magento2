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

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Retrieve new invoice collection from an array of invoices' data
     *
     * @param array $invoicesData
     * @return \Magento\Framework\Data\Collection
     */
    protected function _getInvoiceCollection(array $invoicesData)
    {
        $className = 'Magento\Sales\Model\Order\Invoice';
        $result = new \Magento\Framework\Data\Collection(
            $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false)
        );
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array(
            'orderFactory' => $this->getMock('Magento\Sales\Model\OrderFactory', array(), array(), '', false),
            'orderResourceFactory' => $this->getMock(
                'Magento\Sales\Model\Resource\OrderFactory',
                array(),
                array(),
                '',
                false
            ),
            'calculatorFactory' => $this->getMock(
                    'Magento\Framework\Math\CalculatorFactory',
                    array(),
                    array(),
                    '',
                    false
                ),
            'invoiceItemCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\Resource\Order\Invoice\Item\CollectionFactory',
                array(),
                array(),
                '',
                false
            ),
            'invoiceCommentFactory' => $this->getMock(
                'Magento\Sales\Model\Order\Invoice\CommentFactory',
                array(),
                array(),
                '',
                false
            ),
            'commentCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\Resource\Order\Invoice\Comment\CollectionFactory',
                array(),
                array(),
                '',
                false
            )
        );
        foreach ($invoicesData as $oneInvoiceData) {
            $arguments['data'] = $oneInvoiceData;
            $arguments = $objectManagerHelper->getConstructArguments($className, $arguments);
            /** @var $prevInvoice \Magento\Sales\Model\Order\Invoice */
            $prevInvoice = $this->getMock($className, array('_init'), $arguments);
            $result->addItem($prevInvoice);
        }
        return $result;
    }

    /**
     * @dataProvider collectDataProvider
     * @param array $prevInvoicesData
     * @param float $orderShipping
     * @param float $invoiceShipping
     * @param float $expectedShipping
     */
    public function testCollect(array $prevInvoicesData, $orderShipping, $invoiceShipping, $expectedShipping)
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array(
            'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false),
            'orderItemCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\Resource\Order\Item\CollectionFactory',
                array(),
                array(),
                '',
                false
            ),
            'serviceOrderFactory' => $this->getMock(
                'Magento\Sales\Model\Service\OrderFactory',
                array(),
                array(),
                '',
                false
            ),
            'currencyFactory' => $this->getMock(
                'Magento\Directory\Model\CurrencyFactory',
                array(),
                array(),
                '',
                false
            ),
            'orderHistoryFactory' => $this->getMock(
                'Magento\Sales\Model\Order\Status\HistoryFactory',
                array(),
                array(),
                '',
                false
            ),
            'orderTaxCollectionFactory' => $this->getMock(
                'Magento\Tax\Model\Resource\Sales\Order\Tax\CollectionFactory',
                array(),
                array(),
                '',
                false
            )
        );
        $orderConstructorArgs = $objectManager->getConstructArguments('Magento\Sales\Model\Order', $arguments);
        /** @var $order \Magento\Sales\Model\Order|PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            array('_init', 'getInvoiceCollection', '__wakeup'),
            $orderConstructorArgs,
            '',
            false
        );
        $order->setData('shipping_amount', $orderShipping);
        $order->expects(
            $this->any()
        )->method(
            'getInvoiceCollection'
        )->will(
            $this->returnValue($this->_getInvoiceCollection($prevInvoicesData))
        );
        /** @var $invoice \Magento\Sales\Model\Order\Invoice|PHPUnit_Framework_MockObject_MockObject */
        $invoice = $this->getMock('Magento\Sales\Model\Order\Invoice', array('_init', '__wakeup'), array(), '', false);
        $invoice->setData('shipping_amount', $invoiceShipping);
        $invoice->setOrder($order);

        $total = new \Magento\Sales\Model\Order\Invoice\Total\Shipping();
        $total->collect($invoice);

        $this->assertEquals($expectedShipping, $invoice->getShippingAmount());
    }

    public static function collectDataProvider()
    {
        return array(
            'no previous invoices' => array(
                'prevInvoicesData' => array(array()),
                'orderShipping' => 10.00,
                'invoiceShipping' => 5.00,
                'expectedShipping' => 10.00
            ),
            'zero shipping in previous invoices' => array(
                'prevInvoicesData' => array(array('shipping_amount' => '0.0000')),
                'orderShipping' => 10.00,
                'invoiceShipping' => 5.00,
                'expectedShipping' => 10.00
            ),
            'non-zero shipping in previous invoices' => array(
                'prevInvoicesData' => array(array('shipping_amount' => '10.000')),
                'orderShipping' => 10.00,
                'invoiceShipping' => 5.00,
                'expectedShipping' => 0
            )
        );
    }
}
