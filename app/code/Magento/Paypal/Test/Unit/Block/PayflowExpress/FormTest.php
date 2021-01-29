<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\PayflowExpress;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Block\PayflowExpress\Form;
use Magento\Paypal\Model\Config;

class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_paypalConfig;

    /**
     * @var Form
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_paypalConfig = $this->createMock(\Magento\Paypal\Model\Config::class);
        $this->_paypalConfig
            ->expects($this->once())
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

        $localeResolver = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);

        $helper = new ObjectManager($this);
        $this->_model = $helper->getObject(
            \Magento\Paypal\Block\PayflowExpress\Form::class,
            [
                'paypalConfigFactory' => $paypalConfigFactory,
                'layout' => $layout,
                'localeResolver' => $localeResolver
            ]
        );
    }

    public function testGetBillingAgreementCode()
    {
        $this->assertFalse($this->_model->getBillingAgreementCode());
    }
}
