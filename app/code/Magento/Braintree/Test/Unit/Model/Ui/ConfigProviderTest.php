<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Model\Ui;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Braintree\Gateway\Config\PayPal\Config as PayPalConfig;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProviderTest
 *
 * Test for class \Magento\Braintree\Model\Ui\ConfigProvider
 */
class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    const SDK_URL = 'https://js.braintreegateway.com/v2/braintree.js';

    const CLIENT_TOKEN = 'token';

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var PayPalConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payPalConfig;

    /**
     * @var BraintreeAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $braintreeAdapter;

    /**
     * @var ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
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

        $this->payPalConfig = $this->getMockBuilder(PayPalConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreeAdapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);

        $this->configProvider = new ConfigProvider(
            $this->config,
            $this->payPalConfig,
            $this->braintreeAdapter,
            $this->localeResolver
        );
    }

    /**
     * Run test getConfig method
     *
     * @param array $config
     * @param array $expected
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($config, $expected)
    {
        $this->braintreeAdapter->expects(static::once())
            ->method('generate')
            ->willReturn(self::CLIENT_TOKEN);

        foreach ($config as $method => $value) {
            $this->config->expects(static::once())
                ->method($method)
                ->willReturn($value);
        }

        $this->payPalConfig->expects(static::once())
            ->method('isActive')
            ->willReturn(true);

        $this->payPalConfig->expects(static::once())
            ->method('isAllowToEditShippingAddress')
            ->willReturn(true);

        $this->payPalConfig->expects(static::once())
            ->method('getMerchantName')
            ->willReturn('Test');

        $this->payPalConfig->expects(static::once())
            ->method('getTitle')
            ->willReturn('Payment Title');

        $this->localeResolver->expects(static::once())
            ->method('getLocale')
            ->willReturn('en_US');

        static::assertEquals($expected, $this->configProvider->getConfig());
    }

    /**
     * @covers \Magento\Braintree\Model\Ui\ConfigProvider::getClientToken
     */
    public function testGetClientToken()
    {
        $this->braintreeAdapter->expects(static::once())
            ->method('generate')
            ->willReturn(self::CLIENT_TOKEN);

        static::assertEquals(self::CLIENT_TOKEN, $this->configProvider->getClientToken());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'config' => [
                    'isActive' => true,
                    'getCcTypesMapper' => ['visa' => 'VI', 'american-express'=> 'AE'],
                    'getSdkUrl' => self::SDK_URL,
                    'getCountrySpecificCardTypeConfig' => [
                        'GB' => ['VI', 'AE'],
                        'US' => ['DI', 'JCB']
                    ],
                    'getAvailableCardTypes' => ['AE', 'VI', 'MC', 'DI', 'JCB'],
                    'isCvvEnabled' => true,
                    'isVerify3DSecure' => true,
                    'getThresholdAmount' => 20,
                    'get3DSecureSpecificCountries' => ['GB', 'US', 'CA'],
                    'getEnvironment' => 'test-environment',
                    'getKountMerchantId' => 'test-kount-merchant-id',
                    'getMerchantId' => 'test-merchant-id',
                    'hasFraudProtection' => true,
                ],
                'expected' => [
                    'payment' => [
                        ConfigProvider::CODE => [
                            'isActive' => true,
                            'clientToken' => self::CLIENT_TOKEN,
                            'ccTypesMapper' => ['visa' => 'VI', 'american-express' => 'AE'],
                            'sdkUrl' => self::SDK_URL,
                            'countrySpecificCardTypes' =>[
                                'GB' => ['VI', 'AE'],
                                'US' => ['DI', 'JCB']
                            ],
                            'availableCardTypes' => ['AE', 'VI', 'MC', 'DI', 'JCB'],
                            'useCvv' => true,
                            'environment' => 'test-environment',
                            'kountMerchantId' => 'test-kount-merchant-id',
                            'merchantId' => 'test-merchant-id',
                            'hasFraudProtection' => true,
                            'ccVaultCode' => ConfigProvider::CC_VAULT_CODE
                        ],
                        Config::CODE_3DSECURE => [
                            'enabled' => true,
                            'thresholdAmount' => 20,
                            'specificCountries' => ['GB', 'US', 'CA']
                        ],
                        ConfigProvider::PAYPAL_CODE => [
                            'isActive' => true,
                            'title' => 'Payment Title',
                            'isAllowShippingAddressOverride' => true,
                            'merchantName' => 'Test',
                            'locale' => 'en_us',
                            'paymentAcceptanceMarkSrc' =>
                                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png'
                        ]
                    ]
                ]
            ]
        ];
    }
}
