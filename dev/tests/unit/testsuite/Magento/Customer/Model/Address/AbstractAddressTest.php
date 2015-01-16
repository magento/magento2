<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Address;

class AbstractAddressTest extends \PHPUnit_Framework_TestCase
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

    /** @var \Magento\Customer\Model\Resource\Customer|\PHPUnit_Framework_MockObject_MockObject  */
    protected $resourceMock;

    /** @var \Magento\Framework\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject  */
    protected $resourceCollectionMock;

    /** @var \Magento\Customer\Model\Address\AbstractAddress  */
    protected $model;

    protected function setUp()
    {
        $this->contextMock = $this->getMock('Magento\Framework\Model\Context', [], [], '', false);
        $this->registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->directoryDataMock = $this->getMock('Magento\Directory\Helper\Data', [], [], '', false);
        $this->eavConfigMock = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->addressConfigMock = $this->getMock('Magento\Customer\Model\Address\Config', [], [], '', false);
        $this->regionFactoryMock = $this->getMock(
            'Magento\Directory\Model\RegionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->countryFactoryMock = $this->getMock(
            'Magento\Directory\Model\CountryFactory',
            ['create'],
            [],
            '',
            false
        );
        $regionCollectionMock = $this->getMock(
            'Magento\Directory\Model\Resource\Region\Collection',
            [],
            [],
            '',
            false
        );
        $regionCollectionMock->expects($this->any())
            ->method('getSize')
            ->will($this->returnValue(0));
        $countryMock = $this->getMock('Magento\Directory\Model\Country', [], [], '', false);
        $countryMock->expects($this->any())
            ->method('getRegionCollection')
            ->will($this->returnValue($regionCollectionMock));
        $this->countryFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($countryMock));

        $this->resourceMock = $this->getMock('Magento\Customer\Model\Resource\Customer', [], [], '', false);
        $this->resourceCollectionMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db',
            [],
            [],
            '',
            false
        );
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Customer\Model\Address\AbstractAddress',
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

        $this->model->setData([
                'region_id' => 1,
                'region' => '',
                'country_id' => $countryId,
            ]);
        $this->assertEquals('RegionName', $this->model->getRegion());
    }

    public function testGetRegionWithRegion()
    {
        $countryId = 2;
        $this->prepareGetRegion($countryId);

        $this->model->setData([
                'region_id' => '',
                'region' => 2,
                'country_id' => $countryId,
            ]);
        $this->assertEquals('RegionName', $this->model->getRegion());
    }

    public function testGetRegionWithRegionName()
    {
        $this->regionFactoryMock->expects($this->never())->method('create');

        $this->model->setData([
                'region_id' => '',
                'region' => 'RegionName',
            ]);
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

        $this->model->setData([
                'region_id' => 3,
                'region' => '',
                'country_id' => $countryId,
            ]);
        $this->assertEquals('UK', $this->model->getRegionCode());
    }

    public function testGetRegionCodeWithRegion()
    {
        $countryId = 2;
        $this->prepareGetRegionCode($countryId);

        $this->model->setData([
                'region_id' => '',
                'region' => 4,
                'country_id' => $countryId,
            ]);
        $this->assertEquals('UK', $this->model->getRegionCode());
    }

    public function testGetRegionCodeWithRegionName()
    {
        $this->regionFactoryMock->expects($this->never())->method('create');

        $this->model->setData([
                'region_id' => '',
                'region' => 'UK',
            ]);
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
        $region = $this->getMock(
            'Magento\Directory\Model\Region',
            ['getCountryId', 'getName', '__wakeup', 'load'],
            [],
            '',
            false
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
        $region = $this->getMock(
            'Magento\Directory\Model\Region',
            ['getCountryId', 'getCode', '__wakeup', 'load'],
            [],
            '',
            false
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
     * @param $data
     * @param $expected
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate($data, $expected)
    {
        $this->directoryDataMock->expects($this->once())
            ->method('getCountriesWithOptionalZip')
            ->will($this->returnValue([]));

        $this->directoryDataMock->expects($this->never())
            ->method('isRegionRequired');

        $this->model->setData($data);

        $this->assertEquals($expected, $this->model->validate());
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
        ];
        return [
            'firstname' => [
                array_merge(array_diff_key($data, ['firstname' => '']), ['country_id' => $countryId++]),
                ['Please enter the first name.'],
            ],
            'lastname' => [
                array_merge(array_diff_key($data, ['lastname' => '']), ['country_id' => $countryId++]),
                ['Please enter the last name.'],
            ],
            'street' => [
                array_merge(array_diff_key($data, ['street' => '']), ['country_id' => $countryId++]),
                ['Please enter the street.'],
            ],
            'city' => [
                array_merge(array_diff_key($data, ['city' => '']), ['country_id' => $countryId++]),
                ['Please enter the city.'],
            ],
            'telephone' => [
                array_merge(array_diff_key($data, ['telephone' => '']), ['country_id' => $countryId++]),
                ['Please enter the phone number.'],
            ],
            'postcode' => [
                array_merge(array_diff_key($data, ['postcode' => '']), ['country_id' => $countryId++]),
                ['Please enter the zip/postal code.'],
            ],
            'country_id' => [
                array_diff_key($data, ['country_id' => '']),
                ['Please enter the country.'],
            ],
            'validated' => [array_merge($data, ['country_id' => $countryId++]), true],
        ];
    }
}
