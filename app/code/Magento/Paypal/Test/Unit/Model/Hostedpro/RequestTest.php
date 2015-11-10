<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Hostedpro;

use Magento\Framework\Object as DataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Hostedpro\Request
     */
    protected $_model;

    protected $localeResolverMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\Resolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_model = $helper->getObject(
            'Magento\Paypal\Model\Hostedpro\Request',
            [
                'localeResolver' => $this->localeResolverMock
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
            ->will($this->returnValue($payment));
        $order->expects(static::any())
            ->method('getBillingAddress')
            ->will($this->returnValue($billing));
        $order->expects(static::any())
            ->method('getShippingAddress')
            ->will($this->returnValue($shipping));
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
        $paymentMethodMock = $this->getMockBuilder('Magento\Paypal\Model\Hostedpro')
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
}
