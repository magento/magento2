<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Address\Validator;

use Magento\Store\Model\ScopeInterface;

/**
 * Magento\Customer\Model\Address\Validator\Country tests.
 */
class CountryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject  */
    private $directoryDataMock;

    /** @var \Magento\Customer\Model\Address\Validator\Country  */
    private $model;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /**
     * @var \Magento\Directory\Model\AllowedCountries|\PHPUnit_Framework_MockObject_MockObject
     */
    private $allowedCountriesReaderMock;

    /**
     * @var \Magento\Customer\Model\Config\Share|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shareConfigMock;

    protected function setUp()
    {
        $this->directoryDataMock = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->allowedCountriesReaderMock = $this->createPartialMock(
            \Magento\Directory\Model\AllowedCountries::class,
            ['getAllowedCountries']
        );
        $this->shareConfigMock = $this->createPartialMock(
            \Magento\Customer\Model\Config\Share::class,
            ['isGlobalScope']
        );
        $this->model = $this->objectManager->getObject(
            \Magento\Customer\Model\Address\Validator\Country::class,
            [
                'directoryData' => $this->directoryDataMock,
                'allowedCountriesReader' => $this->allowedCountriesReaderMock,
                'shareConfig' => $this->shareConfigMock,
            ]
        );
    }

    /**
     * @param array $data
     * @param array $countryIds
     * @param array $allowedRegions
     * @param array $expected
     * @return void
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $data, array $countryIds, array $allowedRegions, array $expected)
    {
        $addressMock = $this
            ->getMockBuilder(\Magento\Customer\Model\Address\AbstractAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getCountryId',
                    'getRegion',
                    'getRegionId',
                    'getCountryModel',
                ]
            )->getMock();

        $this->directoryDataMock->expects($this->any())
            ->method('isRegionRequired')
            ->willReturn($data['regionRequired']);

        $this->shareConfigMock->method('isGlobalScope')->willReturn(false);
        $this->allowedCountriesReaderMock
            ->method('getAllowedCountries')
            ->with(ScopeInterface::SCOPE_WEBSITE, null)
            ->willReturn($countryIds);

        $addressMock->method('getCountryId')->willReturn($data['country_id']);

        $countryModelMock = $this->getMockBuilder(\Magento\Directory\Model\Country::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegionCollection'])
            ->getMock();

        $addressMock->method('getCountryModel')->willReturn($countryModelMock);

        $regionCollectionMock = $this->getMockBuilder(\Magento\Directory\Model\ResourceModel\Region\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllIds'])
            ->getMock();
        $countryModelMock
            ->expects($this->any())
            ->method('getRegionCollection')
            ->willReturn($regionCollectionMock);
        $regionCollectionMock->expects($this->any())->method('getAllIds')->willReturn($allowedRegions);

        $addressMock->method('getRegionId')->willReturn($data['region_id']);
        $addressMock->method('getRegion')->willReturn(null);

        $actual = $this->model->validate($addressMock);
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
            'region' => '',
            'regionRequired' => false,
            'company' => 'Magento',
            'fax' => '222-22-22',
        ];
        $result = [
            'country_id1' => [
                array_merge($data, ['country_id' => null]),
                [],
                [1],
                ['"countryId" is required. Enter and try again.'],
            ],
            'country_id2' => [
                $data,
                [],
                [1],
                ['Invalid value of "' . $countryId . '" provided for the countryId field.'],
            ],
            'region' => [
                array_merge($data, ['country_id' => $countryId, 'regionRequired' => true]),
                [$countryId++],
                [],
                ['"region" is required. Enter and try again.'],
            ],
            'region_id1' => [
                array_merge($data, ['country_id' => $countryId, 'regionRequired' => true, 'region_id' => '']),
                [$countryId++],
                [1],
                ['"regionId" is required. Enter and try again.'],
            ],
            'region_id2' => [
                array_merge($data, ['country_id' => $countryId, 'region_id' => 2]),
                [$countryId++],
                [1],
                ['Invalid value of "2" provided for the regionId field.'],
            ],
            'validated' => [
                array_merge($data, ['country_id' => $countryId]),
                [$countryId],
                ['1'],
                [],
            ],
        ];

        return $result;
    }
}
