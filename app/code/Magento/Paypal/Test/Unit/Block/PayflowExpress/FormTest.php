<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\PayflowExpress;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Block\PayflowExpress\Form;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    protected $_paypalConfig;

    /**
     * @var Form
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_paypalConfig = $this->createMock(Config::class);
        $this->_paypalConfig
            ->expects($this->once())
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

        $localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);

        $helper = new ObjectManager($this);
        $this->_model = $helper->getObject(
            Form::class,
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
