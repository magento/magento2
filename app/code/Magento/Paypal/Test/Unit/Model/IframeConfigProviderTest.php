<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Paypal\Model\IframeConfigProvider;

class IframeConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfig()
    {
        $urlBuilder = $this->getMockForAbstractClass(
            \Magento\Framework\UrlInterface::class,
            ['getUrl'],
            '',
            false
        );
        $urlBuilder->expects($this->atLeastOnce())->method('getUrl')->willReturn('http://iframe.url');

        $payment = $this->getMockBuilder(\Magento\Paypal\Model\Payflowpro::class)
            ->setMethods(['isAvailable', 'getFrameActionUrl'])
            ->setMockClassName('paymentInstance')
            ->disableOriginalConstructor()
            ->getMock();

        $paymentHelper= $this->createMock(\Magento\Payment\Helper\Data::class);

        $payment->expects($this->atLeastOnce())->method('isAvailable')->willReturn(true);

        $paymentHelper->expects($this->atLeastOnce())->method('getMethodInstance')->willReturn($payment);

        $configProvider = new IframeConfigProvider($paymentHelper, $urlBuilder);
        $configProvider->getConfig();
    }
}
