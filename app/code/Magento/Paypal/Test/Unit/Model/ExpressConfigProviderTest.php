<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\UrlInterface;
use Magento\Paypal\Model\ExpressConfigProvider;
use Magento\Paypal\Model\SmartButtonConfig;
use PHPUnit\Framework\MockObject\MockObject;

class ExpressConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfig()
    {
        $localeResolver = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);
        $localeResolver->expects($this->once())->method('getLocale');

        $configFactory = $this->createPartialMock(\Magento\Paypal\Model\ConfigFactory::class, ['create']);

        $currentCustomer = $this->createMock(\Magento\Customer\Helper\Session\CurrentCustomer::class);
        $currentCustomer->expects($this->atLeastOnce())->method('getCustomerId')->willReturn(12);

        $paymentHelper= $this->createMock(\Magento\Payment\Helper\Data::class);

        $paypalHelper = $this->createMock(\Magento\Paypal\Helper\Data::class);
        $paypalHelper->expects($this->atLeastOnce())->method('shouldAskToCreateBillingAgreement')->willReturn(false);

        $config = $this->createMock(\Magento\Paypal\Model\Config::class);
        $config->expects($this->once())->method('getPaymentMarkWhatIsPaypalUrl');
        $config->expects($this->once())->method('getPaymentMarkImageUrl');
        $config->expects($this->atLeastOnce())->method('setMethod');

        $configFactory->expects($this->once())->method('create')->willReturn($config);

        $payment = $this->getMockBuilder(\Magento\Paypal\Model\Payflowpro::class)
            ->setMethods(['isAvailable', 'getCheckoutRedirectUrl'])
            ->setMockClassName('paymentInstance')
            ->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->atLeastOnce())->method('isAvailable')->willReturn(true);
        $payment->expects($this->atLeastOnce())->method('getCheckoutRedirectUrl')->willReturn('http://redirect.url');
        $paymentHelper->expects($this->atLeastOnce())->method('getMethodInstance')->willReturn($payment);

        /** @var UrlInterface|MockObject $urlBuilderMock */
        $urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);

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
