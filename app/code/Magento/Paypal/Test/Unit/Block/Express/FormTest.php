<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Express;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Block\Express\Form;
use Magento\Paypal\Helper\Data;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Express\Checkout;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paypalData;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paypalConfig;

    /**
     * @var CurrentCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currentCustomer;

    /**
     * @var Form
     */
    protected $_model;

    protected function setUp()
    {
        $this->_paypalData = $this->getMock(
            \Magento\Paypal\Helper\Data::class,
            [],
            [],
            '',
            false
        );

        $this->_paypalConfig = $this->getMock(
            \Magento\Paypal\Model\Config::class,
            [],
            [],
            '',
            false
        );
        $this->_paypalConfig->expects($this->once())
            ->method('setMethod')
            ->will($this->returnSelf());

        $paypalConfigFactory = $this->getMock(
            \Magento\Paypal\Model\ConfigFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $paypalConfigFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->_paypalConfig));

        $mark = $this->getMock(
            \Magento\Framework\View\Element\Template::class,
            [],
            [],
            '',
            false
        );
        $mark->expects($this->once())
            ->method('setTemplate')
            ->will($this->returnSelf());
        $mark->expects($this->any())
            ->method('__call')
            ->will($this->returnSelf());
        $layout = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class
        );
        $layout->expects($this->once())
            ->method('createBlock')
            ->with(\Magento\Framework\View\Element\Template::class)
            ->will($this->returnValue($mark));

        $this->currentCustomer = $this
            ->getMockBuilder(\Magento\Customer\Helper\Session\CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $localeResolver = $this->getMock(
            \Magento\Framework\Locale\ResolverInterface::class,
            [],
            [],
            '',
            false,
            false
        );

        $helper = new ObjectManager($this);
        $this->_model = $helper->getObject(
            \Magento\Paypal\Block\Express\Form::class,
            [
                'paypalData' => $this->_paypalData,
                'paypalConfigFactory' => $paypalConfigFactory,
                'currentCustomer' => $this->currentCustomer,
                'layout' => $layout,
                'localeResolver' => $localeResolver
            ]
        );
    }

    /**
     * @param bool $ask
     * @param string|null $expected
     * @dataProvider getBillingAgreementCodeDataProvider
     */
    public function testGetBillingAgreementCode($ask, $expected)
    {
        $this->currentCustomer->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue('customer id'));
        $this->_paypalData->expects($this->once())
            ->method('shouldAskToCreateBillingAgreement')
            ->with($this->identicalTo($this->_paypalConfig), 'customer id')
            ->will($this->returnValue($ask));
        $this->assertEquals(
            $expected,
            $this->_model->getBillingAgreementCode()
        );
    }

    /**
     * @return array
     */
    public function getBillingAgreementCodeDataProvider()
    {
        return [
            [true, Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT],
            [false, null]
        ];
    }
}
