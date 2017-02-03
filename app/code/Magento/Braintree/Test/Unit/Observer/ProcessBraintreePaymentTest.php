<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Braintree\Observer\ProcessBraintreePayment;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Braintree\Model\PaymentMethod;

/**
 * Class ProcessBraintreePaymentTest
 */
class ProcessBraintreePaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Observer\ProcessBraintreePayment
     */
    protected $processBraintreePaymentObserver;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Model\Config\Cc|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\DB\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionFactoryMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionFactoryMock = $this->getMockBuilder('\Magento\Framework\DB\TransactionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->processBraintreePaymentObserver = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Observer\ProcessBraintreePayment',
            [
                'config' => $this->configMock,
                'transactionFactory' => $this->transactionFactoryMock,
            ]
        );
    }

    public function testProcessBraintreePayment()
    {
        $orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn(new \Magento\Framework\DataObject(['method' => PaymentMethod::METHOD_CODE]));
        $orderMock->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        $this->configMock->expects($this->at(0))
            ->method('getConfigData')
            ->with(ProcessBraintreePayment::CONFIG_PATH_PAYMENT_ACTION)
            ->willReturn(AbstractMethod::ACTION_AUTHORIZE);
        $this->configMock->expects($this->at(1))
            ->method('getConfigData')
            ->with(ProcessBraintreePayment::CONFIG_PATH_CAPTURE_ACTION)
            ->willReturn(PaymentMethod::CAPTURE_ON_SHIPMENT);

        $shipmentMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Shipment')
            ->disableOriginalConstructor()
            ->getMock();
        $shipmentMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);

        $invoiceItemQty = $this->setupOrderShipmentItems($orderMock, $shipmentMock);
        $invoiceMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods(['setRequestedCaptureCase', 'register', 'getOrder'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('prepareInvoice')
            ->with($invoiceItemQty)
            ->willReturn($invoiceMock);

        $invoiceMock->expects($this->once())
            ->method('setRequestedCaptureCase')
            ->with(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoiceMock->expects($this->once())
            ->method('register');
        $invoiceMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $transactionMock = $this->getMockBuilder('\Magento\Framework\DB\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($transactionMock);
        $transactionMock->expects($this->at(0))
            ->method('addObject')
            ->with($invoiceMock)
            ->willReturnSelf();
        $transactionMock->expects($this->at(1))
            ->method('addObject')
            ->with($orderMock)
            ->willReturnSelf();
        $transactionMock->expects($this->once())
            ->method('save');

        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => new \Magento\Framework\DataObject(
                    [
                        'shipment' => $shipmentMock,
                    ]
                ),
            ]
        );

        $this->assertEquals(
            $this->processBraintreePaymentObserver,
            $this->processBraintreePaymentObserver->execute($observer)
        );
    }

    /**
     * @dataProvider processBraintreePaymentSkipDataProvider
     */
    public function testProcessBraintreePaymentSkip($config)
    {
        $index = 0;
        foreach ($config as $key => $value) {
            $this->configMock->expects($this->at($index))
                ->method('getConfigData')
                ->with($key)
                ->willReturn($value);
            $index++;
        }

        $paymentObj = new \Magento\Framework\DataObject(
            [
                'method' => PaymentMethod::METHOD_CODE,
            ]
        );
        $orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentObj);
        $orderMock->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => new \Magento\Framework\DataObject(
                    [
                        'shipment' => new \Magento\Framework\DataObject(
                            [
                                'order' => $orderMock,
                            ]
                        ),
                    ]
                )
            ]
        );

        $this->transactionFactoryMock->expects($this->never())
            ->method('create');
        $this->assertEquals(
            $this->processBraintreePaymentObserver,
            $this->processBraintreePaymentObserver->execute($observer)
        );
    }

    public function processBraintreePaymentSkipDataProvider()
    {
        return [
            'capture_on_invoice' => [
                'config_data' => [
                    ProcessBraintreePayment::CONFIG_PATH_PAYMENT_ACTION => AbstractMethod::ACTION_AUTHORIZE,
                    ProcessBraintreePayment::CONFIG_PATH_CAPTURE_ACTION => PaymentMethod::CAPTURE_ON_INVOICE,
                ]
            ],
            'action_capture' => [
                'config_data' => [
                    ProcessBraintreePayment::CONFIG_PATH_PAYMENT_ACTION => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                ]
            ]
        ];
    }

    protected function setupOrderShipmentItems($orderMock, $shipmentMock)
    {
        //three order items
        $orderItem1Id = '1001';
        $orderItem2Id = '1002';
        $orderItem3Id = '1003';
        $shipment1Qty = 2;
        $shipment3Qty = 3;

        $orderItem1 = new \Magento\Framework\DataObject(
            ['id' => $orderItem1Id]
        );
        $orderItem2 = new \Magento\Framework\DataObject(
            ['id' => $orderItem2Id]
        );
        $orderItem3 = new \Magento\Framework\DataObject(
            ['id' => $orderItem3Id]
        );
        $orderItems = [$orderItem1, $orderItem2, $orderItem3];
        $orderMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn($orderItems);

        //two items shipped
        $shipmentItems = [
            new \Magento\Framework\DataObject(
                [
                    'qty' => $shipment1Qty,
                    'order_item' => $orderItem1,
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'qty' => $shipment3Qty,
                    'order_item' => $orderItem3,
                ]
            ),
        ];
        $shipmentMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn($shipmentItems);

        return [
            $orderItem1Id => $shipment1Qty,
            $orderItem3Id => $shipment3Qty,
            $orderItem2Id => 0,
        ];
    }
}
