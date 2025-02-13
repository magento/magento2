<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Express;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Block\Express\Form;
use Magento\Paypal\Helper\Data;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Express\Checkout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    protected $_paypalData;

    /**
     * @var Config|MockObject
     */
    protected $_paypalConfig;

    /**
     * @var CurrentCustomer|MockObject
     */
    protected $currentCustomer;

    /**
     * @var Form
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_paypalData = $this->createMock(Data::class);

        $this->_paypalConfig = $this->createMock(Config::class);
        $this->_paypalConfig->expects($this->once())
            ->method('setMethod')->willReturnSelf();

        $paypalConfigFactory = $this->createPartialMock(ConfigFactory::class, ['create']);
        $paypalConfigFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->_paypalConfig);

        $mark = $this->createMock(Template::class);
        $mark->expects($this->once())
            ->method('setTemplate')->willReturnSelf();
        $mark->expects($this->any())
            ->method('__call')->willReturnSelf();
        $layout = $this->getMockForAbstractClass(
            LayoutInterface::class
        );
        $layout->expects($this->once())
            ->method('createBlock')
            ->with(Template::class)
            ->willReturn($mark);

        $this->currentCustomer = $this
            ->getMockBuilder(CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);

        $helper = new ObjectManager($this);
        $this->_model = $helper->getObject(
            Form::class,
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
    public static function getBillingAgreementCodeDataProvider()
    {
        return [
            [true, Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT],
            [false, null]
        ];
    }
}
