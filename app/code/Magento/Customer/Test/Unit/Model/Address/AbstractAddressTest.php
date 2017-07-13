<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Address;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractAddressTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject  */
    protected $contextMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject  */
    protected $registryMock;

    /** @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject  */
    protected $directoryDataMock;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject  */
    protected $eavConfigMock;

    /** @var \Magento\Customer\Model\Address\Config|\PHPUnit_Framework_MockObject_MockObject  */
    protected $addressConfigMock;

    /** @var \Magento\Directory\Model\RegionFactory|\PHPUnit_Framework_MockObject_MockObject  */
    protected $regionFactoryMock;

    /** @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject  */
    protected $countryFactoryMock;

    /** @var \Magento\Customer\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject  */
    protected $resourceMock;

    /** @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit_Framework_MockObject_MockObject  */
    protected $resourceCollectionMock;

    /** @var \Magento\Customer\Model\Address\AbstractAddress  */
    protected $model;

    protected function setUp()
    {
        $this->contextMock = $this->createMock(\Magento\Framework\Model\Context::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->directoryDataMock = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->eavConfigMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->addressConfigMock = $this->createMock(\Magento\Customer\Model\Address\Config::class);
        $this->regionFactoryMock = $this->createPartialMock(\Magento\Directory\Model\RegionFactory::class, ['create']);
        $this->countryFactoryMock = $this->createPartialMock(
            \Magento\Directory\Model\CountryFactory::class,
            ['create']
        );
        $regionCollectionMock = $this->createMock(\Magento\Directory\Model\ResourceModel\Region\Collection::class);
        $regionCollectionMock->expects($this->any())
            ->method('getSize')
            ->will($this->returnValue(0));
        $countryMock = $this->createMock(\Magento\Directory\Model\Country::class);
        $countryMock->expects($this->any())
            ->method('getRegionCollection')
            ->will($this->returnValue($regionCollectionMock));
        $this->countryFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($countryMock));

        $this->resourceMock = $this->createMock(\Magento\Customer\Model\ResourceModel\Customer::class);
        $this->resourceCollectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Customer\Model\Address\AbstractAddress::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'directoryData' => $this->directoryDataMock,
                'eavConfig' => $this->eavConfigMock,
                'addressConfig' => $this->addressConfigMock,
                'regionFactory' => $this->regionFactoryMock,
                'countryFactory' => $this->countryFactoryMock,
                'resource' => $this->resourceMock,
                'resourceCollection' => $this->resourceCollectionMock
            ]
        );
    }

    public function testGetRegionWithRegionId()
    {
        $countryId = 1;
        $this->prepareGetRegion($countryId);

        $this->model->setData('region_id', 1);
        $this->model->setData('region', '');
        $this->model->setData('country_id', $countryId);
        $this->assertEquals('RegionName', $this->model->getRegion());
    }

    public function testGetRegionWithRegion()
    {
        $countryId = 2;
        $this->prepareGetRegion($countryId);

        $this->model->setData('region_id', '');
        $this->model->setData('region', 2);
        $this->model->setData('country_id', $countryId);
        $this->assertEquals('RegionName', $this->model->getRegion());
    }

    public function testGetRegionWithRegionName()
    {
        $this->regionFactoryMock->expects($this->never())->method('create');

        $this->model->setData('region_id', '');
        $this->model->setData('region', 'RegionName');
        $this->assertEquals('RegionName', $this->model->getRegion());
    }

    public function testGetRegionWithoutRegion()
    {
        $this->regionFactoryMock->expects($this->never())->method('create');

        $this->assertNull($this->model->getRegion());
    }

    public function testGetRegionCodeWithRegionId()
    {
        $countryId = 1;
        $this->prepareGetRegionCode($countryId);

        $this->model->setData('region_id', 3);
        $this->model->setData('region', '');
        $this->model->setData('country_id', $countryId);
        $this->assertEquals('UK', $this->model->getRegionCode());
    }

    public function testGetRegionCodeWithRegion()
    {
        $countryId = 2;
        $this->prepareGetRegionCode($countryId);

        $this->model->setData('region_id', '');
        $this->model->setData('region', 4);
        $this->model->setData('country_id', $countryId);
        $this->assertEquals('UK', $this->model->getRegionCode());
    }

    public function testGetRegionCodeWithRegionName()
    {
        $this->regionFactoryMock->expects($this->never())->method('create');

        $this->model->setData('region_id', '');
        $this->model->setData('region', 'UK');
        $this->assertEquals('UK', $this->model->getRegionCode());
    }

    public function testGetRegionCodeWithoutRegion()
    {
        $this->regionFactoryMock->expects($this->never())->method('create');

        $this->assertNull($this->model->getRegionCode());
    }

    /**
     * @param $countryId
     */
    protected function prepareGetRegion($countryId, $regionName = 'RegionName')
    {
        $region = $this->createPartialMock(
            \Magento\Directory\Model\Region::class,
            ['getCountryId', 'getName', '__wakeup', 'load']
        );
        $region->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($regionName));
        $region->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue($countryId));
        $this->regionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($region));
    }

    /**
     * @param $countryId
     */
    protected function prepareGetRegionCode($countryId, $regionCode = 'UK')
    {
        $region = $this->createPartialMock(
            \Magento\Directory\Model\Region::class,
            ['getCountryId', 'getCode', '__wakeup', 'load']
        );
        $region->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue($regionCode));
        $region->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue($countryId));
        $this->regionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($region));
    }

    /**
     * Test for setData method
     *
     * @return void
     */
    public function testSetData()
    {
        $key = [
            'key' => 'value'
        ];

        $this->model->setData($key);
        $this->assertEquals($key, $this->model->getData());
    }

    /**
     * Test for setData method with multidimensional array in "key" argument
     *
     * @return void
     */
    public function testSetDataWithMultidimensionalArray()
    {
        $expected = [
            'key' => 'value',
            'street' => 'value1',
        ];

        $key = [
            'key' => 'value',
            'street' => [
                'key1' => 'value1',
            ]
        ];

        $this->model->setData($key);
        $this->assertEquals($expected, $this->model->getData());
    }

    /**
     * Test for setData method with "value" argument
     *
     * @return void
     */
    public function testSetDataWithValue()
    {
        $value = [
            'street' => 'value',
        ];

        $this->model->setData('street', $value);
        $this->assertEquals($value, $this->model->getData());
    }

    /**
     * Test for setData method with "value" argument
     *
     * @return void
     */
    public function testSetDataWithObject()
    {
        $value = [
            'key' => new \Magento\Framework\DataObject(),
        ];
        $expected = [
            'key' => [
                'key' => new \Magento\Framework\DataObject()
            ]
        ];
        $this->model->setData('key', $value);
        $this->assertEquals($expected, $this->model->getData());
    }

    /**
     * @param $data
     * @param $expected
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate($data, $expected)
    {
        $attributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $attributeMock->expects($this->any())
            ->method('getIsRequired')
            ->willReturn(true);

        $this->eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attributeMock));

        $this->directoryDataMock->expects($this->once())
            ->method('getCountriesWithOptionalZip')
            ->will($this->returnValue([]));

        $this->directoryDataMock->expects($this->never())
            ->method('isRegionRequired');

        foreach ($data as $key => $value) {
            $this->model->setData($key, $value);
        }

        $actual = $this->model->validate();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        $countryId = 1;
        $data = [
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'street' => "Street 1\nStreet 2",
            'city' => 'Odessa',
            'telephone' => '555-55-55',
            'country_id' => $countryId,
            'postcode' => 07201,
            'region_id' => 1,
            'company' => 'Magento',
            'fax' => '222-22-22'
        ];
        return [
            'firstname' => [
                array_merge(array_diff_key($data, ['firstname' => '']), ['country_id' => $countryId++]),
                ['firstname is a required field.'],
            ],
            'lastname' => [
                array_merge(array_diff_key($data, ['lastname' => '']), ['country_id' => $countryId++]),
                ['lastname is a required field.'],
            ],
            'street' => [
                array_merge(array_diff_key($data, ['street' => '']), ['country_id' => $countryId++]),
                ['street is a required field.'],
            ],
            'city' => [
                array_merge(array_diff_key($data, ['city' => '']), ['country_id' => $countryId++]),
                ['city is a required field.'],
            ],
            'telephone' => [
                array_merge(array_diff_key($data, ['telephone' => '']), ['country_id' => $countryId++]),
                ['telephone is a required field.'],
            ],
            'postcode' => [
                array_merge(array_diff_key($data, ['postcode' => '']), ['country_id' => $countryId++]),
                ['postcode is a required field.'],
            ],
            'country_id' => [
                array_diff_key($data, ['country_id' => '']),
                ['countryId is a required field.'],
            ],
            'validated' => [array_merge($data, ['country_id' => $countryId++]), true],
        ];
    }
}
