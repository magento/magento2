<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Gateway\Config;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    const METHOD_CODE = 'braintree';

    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->serializerMock = $this->createMock(Json::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'methodCode' => self::METHOD_CODE,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * @param string $encodedValue
     * @param string|array $value
     * @param array $expected
     * @dataProvider getCountrySpecificCardTypeConfigDataProvider
     */
    public function testGetCountrySpecificCardTypeConfig($encodedValue, $value, array $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_COUNTRY_CREDIT_CARD), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($encodedValue);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($encodedValue)
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
            'valid data' => [
                '{"GB":["VI","AE"],"US":["DI","JCB"]}',
                ['GB' => ['VI', 'AE'], 'US' => ['DI', 'JCB']],
                ['GB' => ['VI', 'AE'], 'US' => ['DI', 'JCB']]
            ],
            'non-array value' => [
                '""',
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
     * @covers       \Magento\Braintree\Gateway\Config\Config::getCountryAvailableCardTypes
     * @dataProvider getCountrySpecificCardTypeConfigDataProvider
     * @param string $encodedData
     * @param string|array $data
     * @param array $countryData
     */
    public function testCountryAvailableCardTypes($encodedData, $data, array $countryData)
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_COUNTRY_CREDIT_CARD), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($encodedData);

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->with($encodedData)
            ->willReturn($data);

        foreach ($countryData as $countryId => $types) {
            $result = $this->model->getCountryAvailableCardTypes($countryId);
            static::assertEquals($types, $result);
        }

        if (empty($countryData)) {
            static::assertEquals($data, "");
        }
    }

    /**
     * @covers \Magento\Braintree\Gateway\Config\Config::isCvvEnabled
     */
    public function testUseCvv()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_USE_CVV), ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);

        static::assertEquals(true, $this->model->isCvvEnabled());
    }

    /**
     * @param mixed $data
     * @param boolean $expected
     * @dataProvider verify3DSecureDataProvider
     * @covers \Magento\Braintree\Gateway\Config\Config::isVerify3DSecure
     */
    public function testIsVerify3DSecure($data, $expected)
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_VERIFY_3DSECURE), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($data);
        static::assertEquals($expected, $this->model->isVerify3DSecure());
    }

    /**
     * Get items to verify 3d secure testing
     * @return array
     */
    public function verify3DSecureDataProvider()
    {
        return [
            ['data' => 1, 'expected' => true],
            ['data' => true, 'expected' => true],
            ['data' => '1', 'expected' => true],
            ['data' => 0, 'expected' => false],
            ['data' => '0', 'expected' => false],
            ['data' => false, 'expected' => false],
        ];
    }

    /**
     * @param mixed $data
     * @param double $expected
     * @covers \Magento\Braintree\Gateway\Config\Config::getThresholdAmount
     * @dataProvider thresholdAmountDataProvider
     */
    public function testGetThresholdAmount($data, $expected)
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_THRESHOLD_AMOUNT), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($data);
        static::assertEquals($expected, $this->model->getThresholdAmount());
    }

    /**
     * Get items for testing threshold amount
     * @return array
     */
    public function thresholdAmountDataProvider()
    {
        return [
            ['data' => '23.01', 'expected' => 23.01],
            ['data' => -1.02, 'expected' => -1.02],
            ['data' => true, 'expected' => 1],
            ['data' => 'true', 'expected' => 0],
            ['data' => 'abc', 'expected' => 0],
            ['data' => false, 'expected' => 0],
            ['data' => 'false', 'expected' => 0],
            ['data' => 1, 'expected' => 1],
        ];
    }

    /**
     * @param int $value
     * @param array $expected
     * @covers \Magento\Braintree\Gateway\Config\Config::get3DSecureSpecificCountries
     * @dataProvider threeDSecureSpecificCountriesDataProvider
     */
    public function testGet3DSecureSpecificCountries($value, array $expected)
    {
        $this->scopeConfigMock->expects(static::at(0))
            ->method('getValue')
            ->with($this->getPath(Config::KEY_VERIFY_ALLOW_SPECIFIC), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        if ($value !== Config::VALUE_3DSECURE_ALL) {
            $this->scopeConfigMock->expects(static::at(1))
                ->method('getValue')
                ->with($this->getPath(Config::KEY_VERIFY_SPECIFIC), ScopeInterface::SCOPE_STORE, null)
                ->willReturn('GB,US');
        }
        static::assertEquals($expected, $this->model->get3DSecureSpecificCountries());
    }

    /**
     * Get variations to test specific countries for 3d secure
     * @return array
     */
    public function threeDSecureSpecificCountriesDataProvider()
    {
        return [
            ['configValue' => 0, 'expected' => []],
            ['configValue' => 1, 'expected' => ['GB', 'US']],
        ];
    }

    /**
     * @covers \Magento\Braintree\Gateway\Config\Config::getDynamicDescriptors
     * @param $name
     * @param $phone
     * @param $url
     * @param array $expected
     * @dataProvider descriptorsDataProvider
     */
    public function testGetDynamicDescriptors($name, $phone, $url, array $expected)
    {
        $this->scopeConfigMock->expects(static::at(0))
            ->method('getValue')
            ->with($this->getPath('descriptor_name'), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($name);
        $this->scopeConfigMock->expects(static::at(1))
            ->method('getValue')
            ->with($this->getPath('descriptor_phone'), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($phone);
        $this->scopeConfigMock->expects(static::at(2))
            ->method('getValue')
            ->with($this->getPath('descriptor_url'), ScopeInterface::SCOPE_STORE, null)
            ->willReturn($url);

        $actual = $this->model->getDynamicDescriptors();
        static::assertEquals($expected, $actual);
    }

    /**
     * Get variations to test dynamic descriptors
     * @return array
     */
    public function descriptorsDataProvider()
    {
        $name = 'company * product';
        $phone = '333-22-22-333';
        $url = 'https://test.url.mage.com';
        return [
            [
                $name, $phone, $url,
                'expected' => [
                    'name' => $name, 'phone' => $phone, 'url' => $url
                ]
            ],
            [
                $name, null, null,
                'expected' => [
                    'name' => $name
                ]
            ],
            [
                null, null, $url,
                'expected' => [
                    'url' => $url
                ]
            ],
            [
                null, null, null,
                'expected' => []
            ]
        ];
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
