<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Model;

use Magento\Braintree\Model\Observer;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Braintree\Model\PaymentMethod;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ObserverTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Braintree\Model\Observer
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Braintree\Model\Config\Cc|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Braintree\Model\Vault|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $vaultMock;

    /**
     * @var \Magento\Framework\DB\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionFactoryMock;

    /**
     * @var \Magento\Braintree\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Braintree\Model\PaymentMethod\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalMethodMock;

    /**
     * @var \Magento\Braintree\Model\Config\PayPal|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalConfigMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\Cc')
            ->disableOriginalConstructor()
            ->getMock();
        $this->vaultMock = $this->getMockBuilder('\Magento\Braintree\Model\Vault')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder('\Magento\Braintree\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionFactoryMock = $this->getMockBuilder('\Magento\Framework\DB\TransactionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->paypalMethodMock = $this->getMockBuilder('\Magento\Braintree\Model\PaymentMethod\PayPal')
            ->disableOriginalConstructor()
            ->getMock();
        $this->paypalConfigMock = $this->getMockBuilder('\Magento\Braintree\Model\Config\PayPal')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Braintree\Model\Observer',
            [
                'config' => $this->configMock,
                'vault' => $this->vaultMock,
                'helper' => $this->helperMock,
                'methodPayPal' => $this->paypalMethodMock,
                'paypalConfig' => $this->paypalConfigMock,
                'transactionFactory' => $this->transactionFactoryMock,
            ]
        );
    }

    protected function setupOrderShipmentItems($orderMock, $shipmentMock)
    {
        //three order items
        $orderItem1Id = '1001';
        $orderItem2Id = '1002';
        $orderItem3Id = '1003';
        $shipment1Qty = 2;
        $shipment3Qty = 3;

        $orderItem1 = new \Magento\Framework\Object(
            ['id' => $orderItem1Id]
        );
        $orderItem2 = new \Magento\Framework\Object(
            ['id' => $orderItem2Id]
        );
        $orderItem3 = new \Magento\Framework\Object(
            ['id' => $orderItem3Id]
        );
        $orderItems = [$orderItem1, $orderItem2, $orderItem3];
        $orderMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn($orderItems);

        //two items shipped
        $shipmentItems = [
            new \Magento\Framework\Object(
                [
                    'qty' => $shipment1Qty,
                    'order_item' => $orderItem1,
                ]
            ),
            new \Magento\Framework\Object(
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

    public function testProcessBraintreePayment()
    {
        $orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn(new \Magento\Framework\Object(['method' => PaymentMethod::METHOD_CODE]));
        $orderMock->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        $this->configMock->expects($this->at(0))
            ->method('getConfigData')
            ->with(Observer::CONFIG_PATH_PAYMENT_ACTION)
            ->willReturn(AbstractMethod::ACTION_AUTHORIZE);
        $this->configMock->expects($this->at(1))
            ->method('getConfigData')
            ->with(Observer::CONFIG_PATH_CAPTURE_ACTION)
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

        $observer = new \Magento\Framework\Object(
            [
                'event' => new \Magento\Framework\Object(
                    [
                        'shipment' => $shipmentMock,
                    ]
                ),
            ]
        );

        $this->assertEquals($this->model, $this->model->processBraintreePayment($observer));
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

        $paymentObj = new \Magento\Framework\Object(
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

        $observer = new \Magento\Framework\Object(
            [
                'event' => new \Magento\Framework\Object(
                    [
                        'shipment' => new \Magento\Framework\Object(
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
        $this->assertEquals($this->model, $this->model->processBraintreePayment($observer));
    }

    public function processBraintreePaymentSkipDataProvider()
    {
        return [
            'capture_on_invoice' => [
                'config_data' => [
                    Observer::CONFIG_PATH_PAYMENT_ACTION => AbstractMethod::ACTION_AUTHORIZE,
                    Observer::CONFIG_PATH_CAPTURE_ACTION => PaymentMethod::CAPTURE_ON_INVOICE,
                ]
            ],
            'action_capture' => [
                'config_data' => [
                    Observer::CONFIG_PATH_PAYMENT_ACTION => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                ]
            ]
        ];
    }

    /**
     * @param bool $isActive
     * @param bool $isExistingCustomer
     * @param bool $deleteCustomer
     * @dataProvider deleteBraintreeCustomerDataProvider
     */
    public function testDeleteBraintreeCustomer($isActive, $isExistingCustomer, $deleteCustomer)
    {
        $braintreeCustoemrId = 'braintreeCustomerId';
        $customerId = '10002';
        $customerEmail = 'John@example.com';

        $this->configMock->expects($this->once())
            ->method('isActive')
            ->willReturn($isActive);

        $customer = new \Magento\Framework\Object(
            [
                'id' => $customerId,
                'email' => $customerEmail,
            ]
        );

        $this->helperMock->expects($this->any())
            ->method('generateCustomerId')
            ->with($customerId, $customerEmail)
            ->willReturn($braintreeCustoemrId);

        $observer = new \Magento\Framework\Object(
            [
                'event' => new \Magento\Framework\Object(
                    [
                        'customer' => $customer,
                    ]
                ),
            ]
        );

        $this->vaultMock->expects($this->any())
            ->method('exists')
            ->with($braintreeCustoemrId)
            ->willReturn($isExistingCustomer);

        if ($deleteCustomer) {
            $this->vaultMock->expects($this->once())
                ->method('deleteCustomer')
                ->with($braintreeCustoemrId);
        } else {
            $this->vaultMock->expects($this->never())
                ->method('deleteCustomer');
        }

        $this->assertEquals($this->model, $this->model->deleteBraintreeCustomer($observer));
    }

    public function deleteBraintreeCustomerDataProvider()
    {
        return [
            'not_active' => [
                'is_active' => false,
                'is_existing_customer' => true,
                'delete_customer' => false,
            ],
            'active_not_existing_customer' => [
                'is_active' => true,
                'is_existing_customer' => false,
                'delete_customer' => false,
            ],
            'active_existing_customer' => [
                'is_active' => true,
                'is_existing_customer' => true,
                'delete_customer' => true,
            ],
        ];
    }

    public function testAddPaypalShortcuts()
    {
        $orPosition = 'before';

        $containerMock = $this->getMockBuilder('\Magento\Catalog\Block\ShortcutButtons')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new \Magento\Framework\Object(
            [
                'is_catalog_product' => false,
                'container' => $containerMock,
                'or_position' => $orPosition,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event,
            ]
        );

        $this->paypalMethodMock->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $this->paypalConfigMock->expects($this->once())
            ->method('isShortcutCheckoutEnabled')
            ->willReturn(true);

        $shortcutMock = $this->getMockBuilder('\Magento\Braintree\Block\PayPal\Shortcut')
            ->disableOriginalConstructor()
            ->setMethods(['setShowOrPosition', 'skipShortcutForGuest'])
            ->getMock();
        $shortcutMock->expects($this->once())
            ->method('skipShortcutForGuest')
            ->willReturn(false);
        $shortcutMock->expects($this->once())
            ->method('setShowOrPosition')
            ->with($orPosition);

        $layoutMock = $this->getMock('\Magento\Framework\View\LayoutInterface');
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(
                Observer::PAYPAL_SHORTCUT_BLOCK,
                '',
                [
                    'data' => [
                        'container' => $containerMock,
                    ]
                ]
            )->willReturn($shortcutMock);

        $containerMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $containerMock->expects($this->once())
            ->method('addShortcut')
            ->with($shortcutMock);

        $this->model->addPaypalShortcuts($observer);
    }

    public function testAddPaypalShortcutsNotActive()
    {
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => null,
            ]
        );

        $this->paypalMethodMock->expects($this->once())
            ->method('isActive')
            ->willReturn(false);
        $this->paypalConfigMock->expects($this->never())
            ->method('isShortcutCheckoutEnabled');

        $this->model->addPaypalShortcuts($observer);
    }

    public function testAddPaypalShortcutsNotEnabled()
    {
        $orPosition = 'before';

        $containerMock = $this->getMockBuilder('\Magento\Catalog\Block\ShortcutButtons')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new \Magento\Framework\Object(
            [
                'is_catalog_product' => false,
                'container' => $containerMock,
                'or_position' => $orPosition,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event,
            ]
        );

        $this->paypalMethodMock->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $this->paypalConfigMock->expects($this->once())
            ->method('isShortcutCheckoutEnabled')
            ->willReturn(false);

        $containerMock->expects($this->never())
            ->method('getLayout');
        $this->model->addPaypalShortcuts($observer);
    }

    public function testAddPaypalShortcutsSkipProductView()
    {
        $orPosition = 'before';

        $containerMock = $this->getMockBuilder('\Magento\Catalog\Block\ShortcutButtons')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new \Magento\Framework\Object(
            [
                'is_catalog_product' => true,
                'container' => $containerMock,
                'or_position' => $orPosition,
            ]
        );
        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => $event,
            ]
        );

        $this->paypalMethodMock->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $this->paypalConfigMock->expects($this->once())
            ->method('isShortcutCheckoutEnabled')
            ->willReturn(true);

        $containerMock->expects($this->never())
            ->method('getLayout');
        $this->model->addPaypalShortcuts($observer);
    }
}
