<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BraintreeTwo\Test\Unit\Gateway\Config;

use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Model\Adapter\BraintreeClientToken;
use Magento\BraintreeTwo\Model\Adapter\BraintreeConfiguration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigTest
 *
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    const METHOD_CODE = 'braintree';
    const CLIENT_TOKEN = 'token';

    /**
     * @var Config
     */
    private $model;

    /**
     * @var BraintreeConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $braintreeConfigurationMock;

    /**
     * @var BraintreeClientToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $braintreeClientTokenMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock(ScopeConfigInterface::class);
        $this->braintreeConfigurationMock = $this->getMock(BraintreeConfiguration::class);
        $this->braintreeClientTokenMock = $this->getMock(BraintreeClientToken::class);

        $this->model = new Config(
            $this->scopeConfigMock,
            $this->braintreeConfigurationMock,
            $this->braintreeClientTokenMock,
            self::METHOD_CODE
        );
    }

    /**
     * Test initCredentials call in constructor when payment is active
     *
     * @covers \Magento\BraintreeTwo\Gateway\Config\Config::initCredentials
     */
    public function testConstructorActive()
    {
        $environment = \Magento\BraintreeTwo\Model\Adminhtml\Source\Environment::ENVIRONMENT_PRODUCTION;
        $merchantId = 'merchantId';
        $publicKey = 'public_key';
        $privateKey = 'private_key';

        $this->scopeConfigMock->expects(static::exactly(5))
            ->method('getValue')
            ->willReturnMap(
                [
                    [$this->getPath(Config::KEY_ACTIVE), ScopeInterface::SCOPE_STORE, null, 1],
                    [$this->getPath(Config::KEY_ENVIRONMENT), ScopeInterface::SCOPE_STORE, null, $environment],
                    [$this->getPath(Config::KEY_MERCHANT_ID), ScopeInterface::SCOPE_STORE, null, $merchantId],
                    [$this->getPath(Config::KEY_PUBLIC_KEY), ScopeInterface::SCOPE_STORE, null, $publicKey],
                    [$this->getPath(Config::KEY_PRIVATE_KEY), ScopeInterface::SCOPE_STORE, null, $privateKey]
                ]
            );

        $this->braintreeConfigurationMock->expects(static::once())
            ->method('environment')
            ->with($environment);
        $this->braintreeConfigurationMock->expects(static::once())
            ->method('merchantId')
            ->with($merchantId);
        $this->braintreeConfigurationMock->expects(static::once())
            ->method('publicKey')
            ->with($publicKey);
        $this->braintreeConfigurationMock->expects(static::once())
            ->method('privateKey')
            ->with($privateKey);

        $this->model = new Config(
            $this->scopeConfigMock,
            $this->braintreeConfigurationMock,
            $this->braintreeClientTokenMock,
            self::METHOD_CODE
        );
    }

    /**
     * Test constructor when payment is inactive
     */
    public function testConstructorInActive()
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_ACTIVE), ScopeInterface::SCOPE_STORE, null)
            ->willReturn(0);

        $this->braintreeConfigurationMock->expects(static::never())
            ->method('environment');
        $this->braintreeConfigurationMock->expects(static::never())
            ->method('merchantId');
        $this->braintreeConfigurationMock->expects(static::never())
            ->method('publicKey');
        $this->braintreeConfigurationMock->expects(static::never())
            ->method('privateKey');

        $this->model = new Config(
            $this->scopeConfigMock,
            $this->braintreeConfigurationMock,
            $this->braintreeClientTokenMock,
            self::METHOD_CODE
        );
    }

    public function testGetClientToken()
    {
        $this->braintreeClientTokenMock->expects(static::once())
            ->method('generate')
            ->willReturn(self::CLIENT_TOKEN);

        static::assertEquals(self::CLIENT_TOKEN, $this->model->getClientToken());
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider getCountrySpecificCardTypeConfigDataProvider
     */
    public function testGetCountrySpecificCardTypeConfig($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_COUNTRY_CREDIT_CARD), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->model->getCountrySpecificCardTypeConfig()
        );
    }

    /**
     * @return array
     */
    public function getCountrySpecificCardTypeConfigDataProvider()
    {
        return [
            [
                serialize(['GB' => ['VI', 'AE'], 'US' => ['DI', 'JCB']]),
                ['GB' => ['VI', 'AE'], 'US' => ['DI', 'JCB']]
            ],
            [
                '',
                []
            ]
        ];
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider getAvailableCardTypesDataProvider
     */
    public function testGetAvailableCardTypes($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_CC_TYPES), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->model->getAvailableCardTypes()
        );
    }

    /**
     * @return array
     */
    public function getAvailableCardTypesDataProvider()
    {
        return [
            [
                'AE,VI,MC,DI,JCB',
                ['AE', 'VI', 'MC', 'DI', 'JCB']
            ],
            [
                '',
                []
            ]
        ];
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider getCcTypesMapperDataProvider
     */
    public function testGetCcTypesMapper($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_CC_TYPES_BRAINTREE_MAPPER), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->model->getCctypesMapper()
        );
    }

    /**
     * @return array
     */
    public function getCcTypesMapperDataProvider()
    {
        return [
            [
                '{"visa":"VI","american-express":"AE"}',
                ['visa' => 'VI', 'american-express' => 'AE']
            ],
            [
                '{invalid json}',
                []
            ],
            [
                '',
                []
            ]
        ];
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Config\Config::getCountryAvailableCardTypes
     * @dataProvider getCountrySpecificCardTypeConfigDataProvider
     */
    public function testCountryAvailableCardTypes($data, $countryData)
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_COUNTRY_CREDIT_CARD), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($data);

        foreach ($countryData as $countryId => $types) {
            $result = $this->model->getCountryAvailableCardTypes($countryId);
            static::assertEquals($types, $result);
        }
    }

    public function testUseCvv()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_USE_CVV), ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);

        static::assertEquals(true, $this->model->isCvvEnabled());
    }

    /**
     * Return config path
     *
     * @param string $field
     * @return string
     */
    private function getPath($field)
    {
        return sprintf(Config::DEFAULT_PATH_PATTERN, self::METHOD_CODE, $field);
    }
}
