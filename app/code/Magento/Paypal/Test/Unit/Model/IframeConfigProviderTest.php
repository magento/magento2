<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Paypal\Model\IframeConfigProvider;
use Magento\Paypal\Model\Payflowpro;
use PHPUnit\Framework\TestCase;

class IframeConfigProviderTest extends TestCase
{
    use MockCreationTrait;

    public function testGetConfig()
    {
        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->expects($this->atLeastOnce())->method('getUrl')->willReturn('http://iframe.url');

        $payment = $this->createPartialMockWithReflection(
            Payflowpro::class,
            ['getFrameActionUrl', 'isAvailable']
        );

        $paymentHelper= $this->createMock(Data::class);

        $payment->expects($this->atLeastOnce())->method('isAvailable')->willReturn(true);

        $paymentHelper->expects($this->atLeastOnce())->method('getMethodInstance')->willReturn($payment);

        $configProvider = new IframeConfigProvider($paymentHelper, $urlBuilder);
        $configProvider->getConfig();
    }
}
