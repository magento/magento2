<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Ui\PayPal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Framework\Locale\ResolverInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ConfigProviderTest
 *
 * Test for class \Magento\Braintree\Model\Ui\PayPal\ConfigProvider
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);

        $this->configProvider = new ConfigProvider(
            $this->config,
            $this->localeResolver
        );
    }

    /**
     * Run test getConfig method.
     *
     * @param array $expected
     * @return void
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($expected)
    {
        $this->config->method('isActive')
            ->willReturn(true);

        $this->config->method('isAllowToEditShippingAddress')
            ->willReturn(true);

        $this->config->method('getMerchantName')
            ->willReturn('Test');

        $this->config->method('getTitle')
            ->willReturn('Payment Title');

        $this->localeResolver->method('getLocale')
            ->willReturn('en_US');

        $this->config->method('isSkipOrderReview')
            ->willReturn(false);

        $this->config->method('getPayPalIcon')
            ->willReturn([
                'width' => 30, 'height' => 26, 'url' => 'https://icon.test.url'
            ]);

        $this->config->method('isRequiredBillingAddress')
            ->willReturn(1);

        self::assertEquals($expected, $this->configProvider->getConfig());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider(): array
    {
        return [
            [
                'expected' => [
                    'payment' => [
                        ConfigProvider::PAYPAL_CODE => [
                            'isActive' => true,
                            'title' => 'Payment Title',
                            'isAllowShippingAddressOverride' => true,
                            'merchantName' => 'Test',
                            'locale' => 'en_US',
                            'paymentAcceptanceMarkSrc' =>
                                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png',
                            'vaultCode' => ConfigProvider::PAYPAL_VAULT_CODE,
                            'skipOrderReview' => false,
                            'paymentIcon' => [
                                'width' => 30, 'height' => 26, 'url' => 'https://icon.test.url'
                            ],
                            'isRequiredBillingAddress' => true,
                        ]
                    ]
                ]
            ]
        ];
    }
}
