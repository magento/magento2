<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Centinel\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    public function testPaymentFormBlockToHtmlBefore()
    {
        $method = $this->getMock(
            'Magento\Paypal\Model\Payflowpro',
            ['getIsCentinelValidationEnabled', 'getCode'],
            [],
            '',
            false
        );
        $method->expects($this->once())
            ->method('getIsCentinelValidationEnabled')
            ->will($this->returnValue(true));

        $method->expects($this->once())
            ->method('getCode')
            ->willReturn('payflowpro');

        $blockLogo = $this->getMock(
            'Magento\Centinel\Block\Logo',
            ['setMethod'],
            [],
            '',
            false
        );
        $blockLogo->expects($this->once())
            ->method('setMethod')
            ->with($method);

        $layout = $this->getMock(
            'Magento\Framework\View\Layout',
            ['createBlock'],
            [],
            '',
            false
        );
        $layout->expects($this->once())
            ->method('createBlock')
            ->will($this->returnValue($blockLogo));

        $block = $this->getMock(
            'Magento\Payment\Block\Form\Cc',
            ['getMethod', 'getLayout', 'setChild'],
            [],
            '',
            false
        );
        $block->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue($method));
        $block->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layout));
        $block->expects($this->once())
            ->method('setChild')
            ->with('payment.method.payflowprocentinel.logo', $blockLogo);

        $event = $this->getMock(
            'Magento\Framework\Event',
            ['getBlock'],
            [],
            '',
            false
        );
        $event->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($block));

        $observer = $this->getMock(
            'Magento\Framework\Event\Observer',
            [],
            [],
            '',
            false
        );
        $observer->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($event));

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $model = $this->objectManager->getObject('Magento\Centinel\Model\Observer');

        $this->assertEquals($model->paymentFormBlockToHtmlBefore($observer), $model);
    }
}
