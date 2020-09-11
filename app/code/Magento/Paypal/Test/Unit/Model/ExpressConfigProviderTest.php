<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\ExpressConfigProvider;
use Magento\Paypal\Model\Payflowpro;
use Magento\Paypal\Model\SmartButtonConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExpressConfigProviderTest extends TestCase
{
    public function testGetConfig()
    {
        $localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $localeResolver->expects($this->once())->method('getLocale');

        $configFactory = $this->createPartialMock(ConfigFactory::class, ['create']);

        $currentCustomer = $this->createMock(CurrentCustomer::class);
        $currentCustomer->expects($this->atLeastOnce())->method('getCustomerId')->willReturn(12);

        $paymentHelper= $this->createMock(Data::class);

        $paypalHelper = $this->createMock(\Magento\Paypal\Helper\Data::class);
        $paypalHelper->expects($this->atLeastOnce())->method('shouldAskToCreateBillingAgreement')->willReturn(false);

        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('getPaymentMarkWhatIsPaypalUrl');
        $config->expects($this->once())->method('getPaymentMarkImageUrl');
        $config->expects($this->atLeastOnce())->method('setMethod');

        $configFactory->expects($this->once())->method('create')->willReturn($config);

        $payment = $this->getMockBuilder(Payflowpro::class)
            ->setMethods(['isAvailable', 'getCheckoutRedirectUrl'])
            ->setMockClassName('paymentInstance')
            ->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->atLeastOnce())->method('isAvailable')->willReturn(true);
        $payment->expects($this->atLeastOnce())->method('getCheckoutRedirectUrl')->willReturn('http://redirect.url');
        $paymentHelper->expects($this->atLeastOnce())->method('getMethodInstance')->willReturn($payment);

        /** @var UrlInterface|MockObject $urlBuilderMock */
        $urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);

        $smartButtonConfigMock = $this->createMock(SmartButtonConfig::class);

        $configProvider = new ExpressConfigProvider(
            $configFactory,
            $localeResolver,
            $currentCustomer,
            $paypalHelper,
            $paymentHelper,
            $urlBuilderMock,
            $smartButtonConfigMock
        );
        $configProvider->getConfig();
    }
}
