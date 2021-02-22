<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Helper;

use Magento\Directory\Helper\Data;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_countryCollection;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_regionCollection;

    /**
     * @var \Magento\Framework\Json\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_store;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_object;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $context = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $context->method('getRequest')
            ->willReturn($requestMock);
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $configCacheType = $this->createMock(\Magento\Framework\App\Cache\Type\Config::class);

        $this->_countryCollection = $this->createMock(\Magento\Directory\Model\ResourceModel\Country\Collection::class);

        $this->_regionCollection = $this->createMock(\Magento\Directory\Model\ResourceModel\Region\Collection::class);
        $regCollectionFactory = $this->createPartialMock(
            \Magento\Directory\Model\ResourceModel\Region\CollectionFactory::class,
            ['create']
        );
        $regCollectionFactory->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $this->_regionCollection
        );

        $this->jsonHelperMock = $this->createMock(\Magento\Framework\Json\Helper\Data::class);

        $this->_store = $this->createMock(\Magento\Store\Model\Store::class);
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($this->_store);

        $currencyFactory = $this->createMock(\Magento\Directory\Model\CurrencyFactory::class);

        $arguments = [
            'context' => $context,
            'configCacheType' => $configCacheType,
            'countryCollection' => $this->_countryCollection,
            'regCollectionFactory' => $regCollectionFactory,
            'jsonHelper' => $this->jsonHelperMock,
            'storeManager' => $storeManager,
            'currencyFactory' => $currencyFactory,
        ];
        $this->_object = $objectManager->getObject(\Magento\Directory\Helper\Data::class, $arguments);
    }

    public function testGetRegionJson()
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturnMap(
                [
                    [
                        AllowedCountries::ALLOWED_COUNTRIES_PATH,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'Country1,Country2'
                    ],
                    [Data::XML_PATH_STATES_REQUIRED, ScopeInterface::SCOPE_STORE, null, '']
                ]
            );
        $regions = [
            new \Magento\Framework\DataObject(
                ['country_id' => 'Country1', 'region_id' => 'r1', 'code' => 'r1-code', 'name' => 'r1-name']
            ),
            new \Magento\Framework\DataObject(
                ['country_id' => 'Country1', 'region_id' => 'r2', 'code' => 'r2-code', 'name' => 'r2-name']
            ),
            new \Magento\Framework\DataObject(
                ['country_id' => 'Country2', 'region_id' => 'r3', 'code' => 'r3-code', 'name' => 'r3-name']
            )
        ];
        $regionIterator = new \ArrayIterator($regions);

        $this->_regionCollection->expects(
            $this->once()
        )->method(
            'addCountryFilter'
        )->with(
            ['Country1', 'Country2']
        )->willReturnSelf(
            
        );
        $this->_regionCollection->expects($this->once())->method('load');
        $this->_regionCollection->expects(
            $this->once()
        )->method(
            'getIterator'
        )->willReturn(
            $regionIterator
        );

        $expectedDataToEncode = [
            'config' => ['show_all_regions' => false, 'regions_required' => []],
            'Country1' => [
                'r1' => ['code' => 'r1-code', 'name' => 'r1-name'],
                'r2' => ['code' => 'r2-code', 'name' => 'r2-name']
            ],
            'Country2' => ['r3' => ['code' => 'r3-code', 'name' => 'r3-name']]
        ];
        $this->jsonHelperMock->expects(
            $this->once()
        )->method(
            'jsonEncode'
        )->with(
            new \PHPUnit\Framework\Constraint\IsIdentical($expectedDataToEncode)
        )->willReturn(
            'encoded_json'
        );

        // Test
        $result = $this->_object->getRegionJson();
        $this->assertEquals('encoded_json', $result);
    }

    /**
     * @param string $configValue
     * @param mixed $expected
     * @dataProvider countriesCommaListDataProvider
     */
    public function testGetCountriesWithStatesRequired($configValue, $expected)
    {
        $this->scopeConfigMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'general/region/state_required'
        )->willReturn(
            $configValue
        );

        $result = $this->_object->getCountriesWithStatesRequired();
        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $configValue
     * @param mixed $expected
     * @dataProvider countriesCommaListDataProvider
     */
    public function testGetCountriesWithOptionalZip($configValue, $expected)
    {
        $this->scopeConfigMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'general/country/optional_zip_countries'
        )->willReturn(
            $configValue
        );

        $result = $this->_object->getCountriesWithOptionalZip();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public static function countriesCommaListDataProvider()
    {
        return [
            'empty_list' => ['', []],
            'normal_list' => ['Country1,Country2', ['Country1', 'Country2']]
        ];
    }

    public function testGetDefaultCountry()
    {
        $storeId = 'storeId';
        $country = 'country';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Data::XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($country);

        $this->assertEquals($country, $this->_object->getDefaultCountry($storeId));
    }

    public function testGetCountryCollection()
    {
        $this->_countryCollection->expects(
            $this->once()
        )->method(
            'isLoaded'
        )->willReturn(
            0
        );

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->_countryCollection->expects(
            $this->once()
        )->method(
            'loadByStore'
        )->with(
            $store
        );

        $this->_object->getCountryCollection($store);
    }

    /**
     * @param string $topCountriesValue
     * @param array $expectedResult
     * @dataProvider topCountriesDataProvider
     */
    public function testGetTopCountryCodesReturnsParsedConfigurationValue($topCountriesValue, $expectedResult)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')->with(\Magento\Directory\Helper\Data::XML_PATH_TOP_COUNTRIES)
            ->willReturn($topCountriesValue);

        $this->assertEquals($expectedResult, $this->_object->getTopCountryCodes());
    }

    /**
     * @return array
     */
    public function topCountriesDataProvider()
    {
        return [
            [null, []],
            ['', []],
            ['US', ['US']],
            ['US,RU', ['US', 'RU']],
        ];
    }
}
