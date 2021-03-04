<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Model\Ui;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Customer\Model\Session;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Class ConfigProviderTest
 *
 * Test for class \Magento\Braintree\Model\Ui\ConfigProvider
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    const SDK_URL = 'https://js.braintreegateway.com/v2/braintree.js';
    const CLIENT_TOKEN = 'token';
    const MERCHANT_ACCOUNT_ID = '245345';

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var BraintreeAdapter|MockObject
     */
    private $braintreeAdapter;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreeAdapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactoryMock */
        $adapterFactoryMock = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactoryMock->method('create')
            ->willReturn($this->braintreeAdapter);

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();
        $this->session->method('getStoreId')
            ->willReturn(null);

        $this->configProvider = new ConfigProvider(
            $this->config,
            $adapterFactoryMock,
            $this->session
        );
    }

    /**
     * Ensure that get config returns correct data if payment is active or not
     *
     * @param array $config
     * @param array $expected
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($config, $expected)
    {
        if ($config['isActive']) {
            $this->braintreeAdapter->expects($this->once())
                ->method('generate')
                ->willReturn(self::CLIENT_TOKEN);
        } else {
            $config = array_replace_recursive(
                $this->getConfigDataProvider()[0]['config'],
                $config
            );
            $expected = array_replace_recursive(
                $this->getConfigDataProvider()[0]['expected'],
                $expected
            );
            $this->braintreeAdapter->expects($this->never())
                ->method('generate');
        }

        foreach ($config as $method => $value) {
            $this->config->expects($this->once())
                ->method($method)
                ->willReturn($value);
        }

        $this->assertEquals($expected, $this->configProvider->getConfig());
    }

    /**
     * @covers       \Magento\Braintree\Model\Ui\ConfigProvider::getClientToken
     * @dataProvider getClientTokenDataProvider
     * @param $merchantAccountId
     * @param $params
     */
    public function testGetClientToken($merchantAccountId, $params)
    {
        $this->config->expects(static::once())
            ->method('getMerchantAccountId')
            ->willReturn($merchantAccountId);

        $this->braintreeAdapter->expects(static::once())
            ->method('generate')
            ->with($params)
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
                    'getCcTypesMapper' => ['visa' => 'VI', 'american-express' => 'AE'],
                    'getSdkUrl' => self::SDK_URL,
                    'getHostedFieldsSdkUrl' => 'https://sdk.com/test.js',
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
                            'hostedFieldsSdkUrl' => 'https://sdk.com/test.js',
                            'countrySpecificCardTypes' => [
                                'GB' => ['VI', 'AE'],
                                'US' => ['DI', 'JCB']
                            ],
                            'availableCardTypes' => ['AE', 'VI', 'MC', 'DI', 'JCB'],
                            'useCvv' => true,
                            'environment' => 'test-environment',
                            'merchantId' => 'test-merchant-id',
                            'hasFraudProtection' => true,
                            'ccVaultCode' => ConfigProvider::CC_VAULT_CODE
                        ],
                        Config::CODE_3DSECURE => [
                            'enabled' => true,
                            'thresholdAmount' => 20,
                            'specificCountries' => ['GB', 'US', 'CA']
                        ]
                    ]
                ]
            ],
            [
                'config' => [
                    'isActive' => false,
                ],
                'expected' => [
                    'payment' => [
                        ConfigProvider::CODE => [
                            'isActive' => false,
                            'clientToken' => null,
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getClientTokenDataProvider()
    {
        return [
            [
                'merchantAccountId' => '',
                'params' => []
            ],
            [
                'merchantAccountId' => self::MERCHANT_ACCOUNT_ID,
                'params' => ['merchantAccountId' => self::MERCHANT_ACCOUNT_ID]
            ]
        ];
    }
}
