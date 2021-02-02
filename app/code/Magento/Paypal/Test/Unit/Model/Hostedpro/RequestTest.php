<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Hostedpro;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helper;

    /**
     * @var \Magento\Paypal\Model\Hostedpro\Request
     */
    protected $_model;

    protected $localeResolverMock;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    protected function setUp(): void
    {
        $this->helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxData = $this->helper->getObject(\Magento\Tax\Helper\Data::class);

        $this->_model = $this->helper->getObject(
            \Magento\Paypal\Model\Hostedpro\Request::class,
            [
                'localeResolver' => $this->localeResolverMock,
                'taxData' => $this->taxData
            ]
        );
    }

    /**
     * @param $billing
     * @param $shipping
     * @param $billingState
     * @param $state
     * @param $countryId
     * @dataProvider addressesDataProvider
     */
    public function testSetOrderAddresses($billing, $shipping, $billingState, $state, $countryId)
    {
        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPayment', '__wakeup', 'getBillingAddress', 'getShippingAddress'])
            ->getMock();
        $order->expects(static::any())
            ->method('getPayment')
            ->willReturn($payment);
        $order->expects(static::any())
            ->method('getBillingAddress')
            ->willReturn($billing);
        $order->expects(static::any())
            ->method('getShippingAddress')
            ->willReturn($shipping);
        $this->_model->setOrder($order);
        static::assertEquals($billingState, $this->_model->getData('billing_state'));
        static::assertEquals($state, $this->_model->getData('state'));
        static::assertEquals($countryId, $this->_model->getData('billing_country'));
        static::assertEquals($countryId, $this->_model->getData('country'));
    }

    /**
     * @return array
     */
    public function addressesDataProvider()
    {
        $billing = new DataObject([
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'city' => 'City',
            'region_code' => 'CA',
            'postcode' => '12346',
            'country_id' => 'US',
            'street' => '1 Ln Ave',
        ]);
        $shipping = new DataObject([
            'firstname' => 'ShipFirstname',
            'lastname' => 'ShipLastname',
            'city' => 'ShipCity',
            'region' => 'olala',
            'postcode' => '12346',
            'country_id' => 'US',
            'street' => '1 Ln Ave',
        ]);
        $billing2 = new DataObject([
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'city' => 'Culver City',
            'region_code' => 'CA',
            'postcode' => '12346',
            'country_id' => 'US',
            'street' => '1 Ln Ave',
        ]);
        $shipping2 = new DataObject([
            'firstname' => 'ShipFirstname',
            'lastname' => 'ShipLastname',
            'city' => 'ShipCity',
            'postcode' => '12346',
            'country_id' => 'US',
            'street' => '1 Ln Ave',
        ]);
        return [
            [$billing, $shipping, 'CA', 'olala', 'US'],
            [$billing2, $shipping2, 'CA', 'ShipCity', 'US']
        ];
    }

    public function testSetPaymentMethod()
    {
        $expectedData = [
            'paymentaction' => 'authorization',
            'notify_url' => 'https://test.com/notifyurl',
            'cancel_return' => 'https://test.com/cancelurl',
            'return' => 'https://test.com/returnurl',
            'lc' => 'US',
            'template' => 'mobile-iframe',
            'showBillingAddress' => 'false',
            'showShippingAddress' => 'true',
            'showBillingEmail' => 'false',
            'showBillingPhone' => 'false',
            'showCustomerName' => 'false',
            'showCardInfo' => 'true',
            'showHostedThankyouPage' => 'false'
        ];
        $paymentMethodMock = $this->getMockBuilder(\Magento\Paypal\Model\Hostedpro::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $paymentMethodMock->expects($this->once())
            ->method('getConfigData')->with('payment_action')->willReturn('Authorization');
        $paymentMethodMock->expects($this->once())->method('getNotifyUrl')->willReturn('https://test.com/notifyurl');
        $paymentMethodMock->expects($this->once())->method('getCancelUrl')->willReturn('https://test.com/cancelurl');
        $paymentMethodMock->expects($this->once())->method('getReturnUrl')->willReturn('https://test.com/returnurl');
        $this->localeResolverMock->expects($this->once())->method('getLocale')->willReturn('en_US');
        $this->assertEquals($this->_model, $this->_model->setPaymentMethod($paymentMethodMock));
        $this->assertEquals('US', $this->_model->getData('lc'));
        $this->assertEquals($expectedData, $this->_model->getData());
    }

    /**
     * @covers \Magento\Paypal\Model\Hostedpro\Request::setOrder
     */
    public function testSetOrder()
    {
        $expectation = [
            'invoice' => '#000001',
            'address_override' => 'true',
            'currency_code' => 'USD',
            'buyer_email' => 'buyer@email.com',
        ];

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order->expects(static::once())
            ->method('getIncrementId')
            ->willReturn($expectation['invoice']);

        $order->expects(static::once())
            ->method('getBaseCurrencyCode')
            ->willReturn($expectation['currency_code']);

        $order->expects(static::once())
            ->method('getCustomerEmail')
            ->willReturn($expectation['buyer_email']);

        $this->_model->setOrder($order);
        static::assertEquals($expectation, $this->_model->getData());
    }

    /**
     * @covers \Magento\Paypal\Model\Hostedpro\Request::setAmount()
     * @param $subtotal
     * @param $total
     * @param $tax
     * @param $shipping
     * @param $discount
     * @dataProvider amountWithoutTaxDataProvider
     */
    public function testSetAmountWithoutTax($total, $subtotal, $tax, $shipping, $discount)
    {
        $expectation = [
            'subtotal' => $subtotal,
            'total' => $total,
            'tax' => $tax,
            'shipping' => $shipping,
            'discount' => abs($discount)
        ];

        static::assertFalse($this->taxData->priceIncludesTax());

        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment->expects(static::once())
            ->method('getBaseAmountAuthorized')
            ->willReturn($total);

        $order->expects(static::once())
            ->method('getPayment')
            ->willReturn($payment);

        $order->expects(static::once())
            ->method('getBaseDiscountAmount')
            ->willReturn($discount);

        $order->expects(static::once())
            ->method('getBaseTaxAmount')
            ->willReturn($tax);

        $order->expects(static::once())
            ->method('getBaseShippingAmount')
            ->willReturn($shipping);

        $order->expects(static::once())
            ->method('getBaseSubtotal')
            ->willReturn($subtotal);
        $this->_model->setAmount($order);

        static::assertEquals($expectation, $this->_model->getData());
    }

    /**
     * @covers \Magento\Paypal\Model\Hostedpro\Request::setAmount()
     * @param $total
     * @param $subtotal
     * @param $tax
     * @param $shipping
     * @param $discount
     * @dataProvider amountWithoutTaxZeroSubtotalDataProvider
     */
    public function testSetAmountWithoutTaxZeroSubtotal($total, $subtotal, $tax, $shipping, $discount)
    {
        $expectation = [
            'subtotal' => $total,
            'total' => $total,
            'tax' => $tax,
            'shipping' => $shipping,
            'discount' => abs($discount)
        ];

        static::assertFalse($this->taxData->priceIncludesTax());

        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment->expects(static::exactly(2))
            ->method('getBaseAmountAuthorized')
            ->willReturn($total);

        $order->expects(static::exactly(2))
            ->method('getPayment')
            ->willReturn($payment);

        $order->expects(static::once())
            ->method('getBaseDiscountAmount')
            ->willReturn($discount);

        $order->expects(static::once())
            ->method('getBaseTaxAmount')
            ->willReturn($tax);

        $order->expects(static::once())
            ->method('getBaseShippingAmount')
            ->willReturn($shipping);

        $order->expects(static::once())
            ->method('getBaseSubtotal')
            ->willReturn($subtotal);
        $this->_model->setAmount($order);

        static::assertEquals($expectation, $this->_model->getData());
    }

    /**
     * @covers \Magento\Paypal\Model\Hostedpro\Request::setAmount()
     */
    public function testSetAmountWithIncludedTax()
    {
        /** @var \Magento\Tax\Model\Config  $config */
        $config = $this->helper->getObject(\Magento\Tax\Model\Config::class);
        $config->setPriceIncludesTax(true);

        $this->taxData = $this->helper->getObject(
            \Magento\Tax\Helper\Data::class,
            [
                'taxConfig' => $config
            ]
        );

        $this->_model = $this->helper->getObject(
            \Magento\Paypal\Model\Hostedpro\Request::class,
            [
                'localeResolver' => $this->localeResolverMock,
                'taxData' => $this->taxData
            ]
        );

        static::assertTrue($this->taxData->getConfig()->priceIncludesTax());

        $amount = 19.65;

        $expectation = [
            'amount' => $amount,
            'subtotal' => $amount
        ];

        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment->expects(static::once())
            ->method('getBaseAmountAuthorized')
            ->willReturn($amount);

        $order->expects(static::once())
            ->method('getPayment')
            ->willReturn($payment);

        $this->_model->setAmount($order);

        static::assertEquals($expectation, $this->_model->getData());
    }

    /**
     * Get data for amount with tax tests
     * @return array
     */
    public function amountWithoutTaxDataProvider()
    {
        return [
            ['total' => 31.00, 'subtotal' => 10.00, 'tax' => 1.00, 'shipping' => 20.00, 'discount' => 0.00],
            ['total' => 5.00, 'subtotal' => 10.00, 'tax' => 0.00, 'shipping' => 20.00, 'discount' => -25.00],
        ];
    }

    /**
     * @return array
     */
    public function amountWithoutTaxZeroSubtotalDataProvider()
    {
        return [
            ['total' => 10.00, 'subtotal' => 0.00, 'tax' => 0.00, 'shipping' => 20.00, 'discount' => 0.00],
        ];
    }
}
