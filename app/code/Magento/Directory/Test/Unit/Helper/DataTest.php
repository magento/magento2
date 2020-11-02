<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Helper;

use ArrayIterator;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonDataHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /**
     * @var CountryCollection|MockObject
     */
    protected $_countryCollection;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $_regionCollection;

    /**
     * @var JsonDataHelper|MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var Store|MockObject
     */
    protected $_store;

    /**
     * @var RequestInterface|MockObject
     */
    protected $_request;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Data
     */
    protected $_object;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);
        $this->_request = $this->getMockForAbstractClass(RequestInterface::class);
        $context = $this->createMock(Context::class);
        $context->method('getRequest')
            ->willReturn($this->_request);
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);
        $configCacheType = $this->createMock(Config::class);

        $this->_countryCollection = $this->createMock(CountryCollection::class);

        $this->_regionCollection = $this->createMock(RegionCollection::class);
        $regCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $regCollectionFactory->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $this->_regionCollection
        );

        $this->jsonHelperMock = $this->createMock(JsonDataHelper::class);

        $this->_store = $this->createMock(Store::class);
        $this->_storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $currencyFactory = $this->createMock(CurrencyFactory::class);

        $arguments = [
            'context' => $context,
            'configCacheType' => $configCacheType,
            'countryCollection' => $this->_countryCollection,
            'regCollectionFactory' => $regCollectionFactory,
            'jsonHelper' => $this->jsonHelperMock,
            'storeManager' => $this->_storeManager,
            'currencyFactory' => $currencyFactory,
        ];
        $this->_object = $objectManager->getObject(Data::class, $arguments);
    }

    /**
     * @return array
     */
    public function regionJsonProvider(): array
    {
        $countries = [
            'Country1' => [
                'r1' => ['code' => 'r1-code', 'name' => 'r1-name'],
                'r2' => ['code' => 'r2-code', 'name' => 'r2-name']
            ],
            'Country2' => [
                'r3' => ['code' => 'r3-code', 'name' => 'r3-name'],
            ],
            'Country3' => [],
        ];

        return [
            [
                null,
                $countries,
            ],
            [
                null,
                [
                    'Country1' => $countries['Country1'],
                ],
                [ScopeInterface::SCOPE_WEBSITE => 1],
            ],
            [
                1,
                [
                    'Country2' => $countries['Country2'],
                ],
            ],
            [
                null,
                [
                    'Country2' => $countries['Country2'],
                ],
                [
                    ScopeInterface::SCOPE_WEBSITE => null,
                    ScopeInterface::SCOPE_STORE => 1,
                ],
            ],
            [
                2,
                [
                    'Country3' => $countries['Country3'],
                ],
            ],
            [
                null,
                [
                    'Country3' => $countries['Country3'],
                ],
                [ScopeInterface::SCOPE_STORE => 2],
            ],
        ];
    }

    /**
     * @param int|null $currentStoreId
     * @param array $allowedCountries
     * @param array $requestParams
     * @dataProvider regionJsonProvider
     */
    public function testGetRegionJson(?int $currentStoreId, array $allowedCountries, array $requestParams = [])
    {
        if ($currentStoreId) {
            $this->_store->method('getId')->willReturn($currentStoreId);
            $this->_storeManager->expects($this->any())->method('getStore')->willReturn($this->_store);
        } else {
            $this->_storeManager->expects($this->any())->method('getStore')->willReturn(null);
        }

        if ($requestParams) {
            $map = [];

            foreach ($requestParams as $name => $value) {
                $map[] = [$name, null, $value];
            }

            $this->_request
                ->method('getParam')
                ->willReturnMap($map);
        }

        $expectedDataToEncode = array_merge([
            'config' => ['show_all_regions' => false, 'regions_required' => []],
        ], array_filter($allowedCountries));

        $this->scopeConfigMock->method('getValue')
            ->willReturnMap(
                [
                    [
                        AllowedCountries::ALLOWED_COUNTRIES_PATH,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'Country1,Country2,Country3'
                    ],
                    [
                        AllowedCountries::ALLOWED_COUNTRIES_PATH,
                        ScopeInterface::SCOPE_WEBSITE,
                        1,
                        'Country1'
                    ],
                    [
                        AllowedCountries::ALLOWED_COUNTRIES_PATH,
                        ScopeInterface::SCOPE_STORE,
                        1,
                        'Country2'
                    ],
                    [
                        AllowedCountries::ALLOWED_COUNTRIES_PATH,
                        ScopeInterface::SCOPE_STORE,
                        2,
                        'Country3'
                    ],
                    [Data::XML_PATH_STATES_REQUIRED, ScopeInterface::SCOPE_STORE, null, '']
                ]
            );
        $regions = [
            new DataObject(
                ['country_id' => 'Country1', 'region_id' => 'r1', 'code' => 'r1-code', 'name' => 'r1-name']
            ),
            new DataObject(
                ['country_id' => 'Country1', 'region_id' => 'r2', 'code' => 'r2-code', 'name' => 'r2-name']
            ),
            new DataObject(
                ['country_id' => 'Country2', 'region_id' => 'r3', 'code' => 'r3-code', 'name' => 'r3-name']
            )
        ];
        $regionIterator = new ArrayIterator(array_filter($regions, function(DataObject $region) use ($allowedCountries) {
            return array_key_exists($region->getData('country_id'), $allowedCountries);
        }));

        $this->_regionCollection->expects(
            $this->once()
        )->method(
            'addCountryFilter'
        )->with(
            array_keys($allowedCountries)
        )->willReturnSelf();
        $this->_regionCollection->expects($this->once())->method('load');
        $this->_regionCollection->expects(
            $this->once()
        )->method(
            'getIterator'
        )->willReturn(
            $regionIterator
        );

        $this->jsonHelperMock->expects(
            $this->once()
        )->method(
            'jsonEncode'
        )->with(
            new IsIdentical($expectedDataToEncode)
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
                ScopeInterface::SCOPE_STORE,
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

        $store = $this->createMock(Store::class);
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
            ->method('getValue')->with(Data::XML_PATH_TOP_COUNTRIES)
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
