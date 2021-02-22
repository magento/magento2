<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_paypalData;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_paypalConfig;

    /**
     * @var CurrentCustomer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $currentCustomer;

    /**
     * @var Form
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_paypalData = $this->createMock(\Magento\Paypal\Helper\Data::class);

        $this->_paypalConfig = $this->createMock(\Magento\Paypal\Model\Config::class);
        $this->_paypalConfig->expects($this->once())
            ->method('setMethod')
            ->willReturnSelf();

        $paypalConfigFactory = $this->createPartialMock(\Magento\Paypal\Model\ConfigFactory::class, ['create']);
        $paypalConfigFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->_paypalConfig);

        $mark = $this->createMock(\Magento\Framework\View\Element\Template::class);
        $mark->expects($this->once())
            ->method('setTemplate')
            ->willReturnSelf();
        $mark->expects($this->any())
            ->method('__call')
            ->willReturnSelf();
        $layout = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class
        );
        $layout->expects($this->once())
            ->method('createBlock')
            ->with(\Magento\Framework\View\Element\Template::class)
            ->willReturn($mark);

        $this->currentCustomer = $this
            ->getMockBuilder(\Magento\Customer\Helper\Session\CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $localeResolver = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);

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
            ->willReturn('customer id');
        $this->_paypalData->expects($this->once())
            ->method('shouldAskToCreateBillingAgreement')
            ->with($this->identicalTo($this->_paypalConfig), 'customer id')
            ->willReturn($ask);
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
