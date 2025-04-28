<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Paypal\Model\IframeConfigProvider;
use Magento\Paypal\Model\Payflowpro;
use PHPUnit\Framework\TestCase;

class IframeConfigProviderTest extends TestCase
{
    public function testGetConfig()
    {
        $urlBuilder = $this->getMockForAbstractClass(
            UrlInterface::class,
            ['getUrl'],
            '',
            false
        );
        $urlBuilder->expects($this->atLeastOnce())->method('getUrl')->willReturn('http://iframe.url');

        $payment = $this->getMockBuilder(Payflowpro::class)
            ->addMethods(['getFrameActionUrl'])
            ->onlyMethods(['isAvailable'])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentHelper= $this->createMock(Data::class);

        $payment->expects($this->atLeastOnce())->method('isAvailable')->willReturn(true);

        $paymentHelper->expects($this->atLeastOnce())->method('getMethodInstance')->willReturn($payment);

        $configProvider = new IframeConfigProvider($paymentHelper, $urlBuilder);
        $configProvider->getConfig();
    }
}
