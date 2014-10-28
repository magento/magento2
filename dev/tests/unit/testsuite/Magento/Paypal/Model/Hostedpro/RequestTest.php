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
namespace Magento\Paypal\Model\Hostedpro;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Hostedpro\Request
     */
    protected $_model;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Paypal\Model\Hostedpro\Request'
        );
    }

    /**
     * @dataProvider addressesDataProvider
     */
    public function testSetOrderAddresses($billing, $shipping, $billingState, $state)
    {
        $payment = $this->getMock('Magento\Sales\Model\Order\Payment', ['__wakeup'], [], '', false);
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['getPayment', '__wakeup', 'getBillingAddress', 'getShippingAddress'],
            [],
            '',
            false
        );
        $order->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($payment));
        $order->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($billing));
        $order->expects($this->any())
            ->method('getShippingAddress')
            ->will($this->returnValue($shipping));
        $this->_model->setOrder($order);
        $this->assertEquals($billingState, $this->_model->getData('billing_state'));
        $this->assertEquals($state, $this->_model->getData('state'));
    }

    /**
     * @return array
     */
    public function addressesDataProvider()
    {
        $billing = new \Magento\Framework\Object([
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'city' => 'City',
            'region_code' => 'CA',
            'postcode' => '12346',
            'country' => 'United States',
            'Street' => '1 Ln Ave'
        ]);
        $shipping = new \Magento\Framework\Object([
            'firstname' => 'ShipFirstname',
            'lastname' => 'ShipLastname',
            'city' => 'ShipCity',
            'region' => 'olala',
            'postcode' => '12346',
            'country' => 'United States',
            'Street' => '1 Ln Ave'
        ]);
        $billing2 = new \Magento\Framework\Object([
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'city' => 'City',
            'region_code' => 'muuuu',
            'postcode' => '12346',
            'country' => 'United States',
            'Street' => '1 Ln Ave'
        ]);
        $shipping2 = new \Magento\Framework\Object([
            'firstname' => 'ShipFirstname',
            'lastname' => 'ShipLastname',
            'city' => 'ShipCity',
            'postcode' => '12346',
            'country' => 'United States',
            'Street' => '1 Ln Ave'
        ]);
        return [
            [$billing, $shipping, 'CA', 'olala'],
            [$billing2, $shipping2, 'muuuu', 'ShipCity']
        ];
    }
}
