<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Model\Ui;

use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Model\Ui\ConfigProvider;

/**
 * Class ConfigProviderTest
 *
 * Test for class \Magento\BraintreeTwo\Model\Ui\ConfigProvider
 */
class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    const SDK_URL = 'https://js.braintreegateway.com/v2/braintree.js';

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $configProvider = new ConfigProvider($this->configMock);
        foreach ($config as $method => $value) {
            $this->configMock->expects(static::once())
                ->method($method)
                ->willReturn($value);
        }

        static::assertEquals($expected, $configProvider->getConfig());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'config' => [
                    'getClientToken' => 'token',
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
                ],
                'expected' => [
                    'payment' => [
                        ConfigProvider::CODE => [
                            'clientToken' => 'token',
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
