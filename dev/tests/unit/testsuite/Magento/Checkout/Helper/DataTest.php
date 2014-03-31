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
namespace Magento\Checkout\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_transportBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_translator;

    protected function setUp()
    {
        $this->_translator = $this->getMock('Magento\Translate\Inline\StateInterface', array(), array(), '', false);
        $context = $this->getMock('\Magento\App\Helper\Context', array(), array(), '', false);

        $storeConfig = $this->getMock('\Magento\Core\Model\Store\Config', array(), array(), '', false);
        $storeConfig->expects(
            $this->any()
        )->method(
            'getConfig'
        )->will(
            $this->returnValueMap(
                array(
                    array('checkout/payment_failed/template', 8, 'fixture_email_template_payment_failed'),
                    array('checkout/payment_failed/receiver', 8, 'sysadmin'),
                    array('trans_email/ident_sysadmin/email', 8, 'sysadmin@example.com'),
                    array('trans_email/ident_sysadmin/name', 8, 'System Administrator'),
                    array('checkout/payment_failed/identity', 8, 'noreply@example.com'),
                    array('carriers/ground/title', null, 'Ground Shipping'),
                    array('payment/fixture-payment-method/title', null, 'Check Money Order')
                )
            )
        );

        $storeManager = $this->getMock('\Magento\Core\Model\StoreManagerInterface', array(), array(), '', false);

        $checkoutSession = $this->getMock('\Magento\Checkout\Model\Session', array(), array(), '', false);

        $localeDate = $this->getMock('\Magento\Stdlib\DateTime\TimezoneInterface', array(), array(), '', false);
        $localeDate->expects($this->any())->method('date')->will($this->returnValue('Oct 02, 2013'));

        $collectionFactory = $this->getMock(
            '\Magento\Checkout\Model\Resource\Agreement\CollectionFactory',
            array(),
            array(),
            '',
            false
        );

        $this->_transportBuilder = $this->getMock(
            '\Magento\Mail\Template\TransportBuilder',
            array(),
            array(),
            '',
            false
        );

        $this->_helper = new Data(
            $context,
            $storeConfig,
            $storeManager,
            $checkoutSession,
            $localeDate,
            $collectionFactory,
            $this->_transportBuilder,
            $this->_translator
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSendPaymentFailedEmail()
    {
        $shippingAddress = new \Magento\Object(array('shipping_method' => 'ground_transportation'));
        $billingAddress = new \Magento\Object(array('street' => 'Fixture St'));

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'setTemplateOptions'
        )->with(
            array('area' => \Magento\Core\Model\App\Area::AREA_FRONTEND, 'store' => 8)
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'setTemplateIdentifier'
        )->with(
            'fixture_email_template_payment_failed'
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'setFrom'
        )->with(
            'noreply@example.com'
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'addTo'
        )->with(
            'sysadmin@example.com',
            'System Administrator'
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'setTemplateVars'
        )->with(
            array(
                'reason' => 'test message',
                'checkoutType' => 'onepage',
                'dateAndTime' => 'Oct 02, 2013',
                'customer' => 'John Doe',
                'customerEmail' => 'john.doe@example.com',
                'billingAddress' => $billingAddress,
                'shippingAddress' => $shippingAddress,
                'shippingMethod' => 'Ground Shipping',
                'paymentMethod' => 'Check Money Order',
                'items' => "Product One  x 2  USD 10<br />\nProduct Two  x 3  USD 60<br />\n",
                'total' => 'USD 70'
            )
        )->will(
            $this->returnSelf()
        );

        $this->_transportBuilder->expects($this->once())->method('addBcc')->will($this->returnSelf());
        $this->_transportBuilder->expects(
            $this->once()
        )->method(
            'getTransport'
        )->will(
            $this->returnValue($this->getMock('Magento\Mail\TransportInterface'))
        );

        $this->_translator->expects($this->at(1))
            ->method('suspend');
        $this->_translator->expects($this->at(1))
            ->method('resume');

        $productOne = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productOne->expects($this->once())->method('getName')->will($this->returnValue('Product One'));
        $productOne->expects($this->once())->method('getFinalPrice')->with(2)->will($this->returnValue(10));

        $productTwo = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productTwo->expects($this->once())->method('getName')->will($this->returnValue('Product Two'));
        $productTwo->expects($this->once())->method('getFinalPrice')->with(3)->will($this->returnValue(60));

        $quote = new \Magento\Object(
            array(
                'store_id' => 8,
                'store_currency_code' => 'USD',
                'grand_total' => 70,
                'customer_firstname' => 'John',
                'customer_lastname' => 'Doe',
                'customer_email' => 'john.doe@example.com',
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
                'payment' => new \Magento\Object(array('method' => 'fixture-payment-method')),
                'all_visible_items' => array(
                    new \Magento\Object(array('product' => $productOne, 'qty' => 2)),
                    new \Magento\Object(array('product' => $productTwo, 'qty' => 3))
                )
            )
        );
        $this->assertSame($this->_helper, $this->_helper->sendPaymentFailedEmail($quote, 'test message'));
    }
}
