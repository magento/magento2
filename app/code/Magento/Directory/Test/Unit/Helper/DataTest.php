<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Helper;

use Magento\Directory\Helper\Data;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_countryCollection;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_regionCollection;

    /**
     * @var \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_store;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_object;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $context = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $configCacheType = $this->getMock('Magento\Framework\App\Cache\Type\Config', [], [], '', false);

        $this->_countryCollection = $this->getMock(
            'Magento\Directory\Model\ResourceModel\Country\Collection',
            [],
            [],
            '',
            false
        );

        $this->_regionCollection = $this->getMock(
            'Magento\Directory\Model\ResourceModel\Region\Collection',
            [],
            [],
            '',
            false
        );
        $regCollectionFactory = $this->getMock(
            'Magento\Directory\Model\ResourceModel\Region\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $regCollectionFactory->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_regionCollection)
        );

        $this->jsonHelperMock = $this->getMock('Magento\Framework\Json\Helper\Data', [], [], '', false);

        $this->_store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->_store));

        $currencyFactory = $this->getMock('Magento\Directory\Model\CurrencyFactory', [], [], '', false);

        $arguments = [
            'context' => $context,
            'configCacheType' => $configCacheType,
            'countryCollection' => $this->_countryCollection,
            'regCollectionFactory' => $regCollectionFactory,
            'jsonHelper' => $this->jsonHelperMock,
            'storeManager' => $storeManager,
            'currencyFactory' => $currencyFactory,
        ];
        $this->_object = $objectManager->getObject('Magento\Directory\Helper\Data', $arguments);
    }

    public function testGetRegionJson()
    {
        $countries = [
            new \Magento\Framework\DataObject(['country_id' => 'Country1']),
            new \Magento\Framework\DataObject(['country_id' => 'Country2'])
        ];
        $countryIterator = new \ArrayIterator($countries);
        $this->_countryCollection->expects(
            $this->atLeastOnce()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue($countryIterator)
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
        )->will(
            $this->returnSelf()
        );
        $this->_regionCollection->expects($this->once())->method('load');
        $this->_regionCollection->expects(
            $this->once()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue($regionIterator)
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
            new \PHPUnit_Framework_Constraint_IsIdentical($expectedDataToEncode)
        )->will(
            $this->returnValue('encoded_json')
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
        )->will(
            $this->returnValue($configValue)
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
        )->will(
            $this->returnValue($configValue)
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
            )->will($this->returnValue($country));

        $this->assertEquals($country, $this->_object->getDefaultCountry($storeId));
    }

    public function testGetCountryCollection()
    {
        $this->_countryCollection->expects(
            $this->once()
        )->method(
            'isLoaded'
        )->will(
            $this->returnValue(0)
        );

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
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
