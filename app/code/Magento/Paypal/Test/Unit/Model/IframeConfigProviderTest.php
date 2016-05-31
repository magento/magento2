<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Paypal\Model\IframeConfigProvider;

class IframeConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $urlBuilder = $this->getMockForAbstractClass(
            'Magento\Framework\UrlInterface',
            ['getUrl'],
            '',
            false
        );
        $urlBuilder->expects($this->atLeastOnce())->method('getUrl')->willReturn('http://iframe.url');

        $payment = $this->getMock(
            'Magento\Paypal\Model\Payflowpro',
            ['isAvailable', 'getFrameActionUrl'],
            [],
            'paymentInstance',
            false
        );
        $paymentHelper= $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);

        $payment->expects($this->atLeastOnce())->method('isAvailable')->willReturn(true);

        $paymentHelper->expects($this->atLeastOnce())->method('getMethodInstance')->willReturn($payment);

        $configProvider = new IframeConfigProvider($paymentHelper, $urlBuilder);
        $configProvider->getConfig();
    }
}
