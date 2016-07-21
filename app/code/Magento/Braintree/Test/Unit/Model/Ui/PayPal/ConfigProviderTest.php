<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
class ConfigProviderTest extends \PHPUnit_Framework_TestCase
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
     * Run test getConfig method
     *
     * @param array $config
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($expected)
    {
        $this->config->expects(static::once())
            ->method('isActive')
            ->willReturn(true);

        $this->config->expects(static::once())
            ->method('isAllowToEditShippingAddress')
            ->willReturn(true);

        $this->config->expects(static::once())
            ->method('getMerchantName')
            ->willReturn('Test');

        $this->config->expects(static::once())
            ->method('getTitle')
            ->willReturn('Payment Title');

        $this->localeResolver->expects(static::once())
            ->method('getLocale')
            ->willReturn('en_US');

        $this->config->expects(static::once())
            ->method('isSkipOrderReview')
            ->willReturn(false);

        $this->config->expects(static::once())
            ->method('getPayPalIcon')
            ->willReturn([
                'width' => 30, 'height' => 26, 'url' => 'https://icon.test.url'
            ]);

        static::assertEquals($expected, $this->configProvider->getConfig());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
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
                            'locale' => 'en_us',
                            'paymentAcceptanceMarkSrc' =>
                                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png',
                            'vaultCode' => ConfigProvider::PAYPAL_VAULT_CODE,
                            'skipOrderReview' => false,
                            'paymentIcon' => [
                                'width' => 30, 'height' => 26, 'url' => 'https://icon.test.url'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
