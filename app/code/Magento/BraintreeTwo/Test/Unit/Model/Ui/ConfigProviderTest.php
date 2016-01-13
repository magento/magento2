<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Model\Ui;

use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Model\Adapter\BraintreeAdapter;
use Magento\BraintreeTwo\Model\Ui\ConfigProvider;

/**
 * Class ConfigProviderTest
 *
 * Test for class \Magento\BraintreeTwo\Model\Ui\ConfigProvider
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
     * @var BraintreeAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $braintreeAdapter;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeAdapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = new ConfigProvider($this->config, $this->braintreeAdapter);
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

        static::assertEquals($expected, $this->configProvider->getConfig());
    }

    /**
     * @covers \Magento\BraintreeTwo\Model\Ui\ConfigProvider::getClientToken
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
                    'hasFraudProtection' => true
                ],
                'expected' => [
                    'payment' => [
                        ConfigProvider::CODE => [
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
                            'hasFraudProtection' => true
                        ],
                        Config::CODE_3DSECURE => [
                            'enabled' => true,
                            'thresholdAmount' => 20,
                            'specificCountries' => ['GB', 'US', 'CA']
                        ]
                    ]
                ]
            ]
        ];
    }
}
