<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Address\Validator;

/**
 * Magento\Customer\Model\Address\Validator\General tests.
 */
class GeneralTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject  */
    private $directoryDataMock;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject  */
    private $eavConfigMock;

    /** @var \Magento\Customer\Model\Address\Validator\General  */
    private $model;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->directoryDataMock = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->eavConfigMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            \Magento\Customer\Model\Address\Validator\General::class,
            [
                'eavConfig' => $this->eavConfigMock,
                'directoryData' => $this->directoryDataMock,
            ]
        );
    }

    /**
     * @param array $data
     * @param array $expected
     * @return void
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $data, array $expected)
    {
        $addressMock = $this
            ->getMockBuilder(\Magento\Customer\Model\Address\AbstractAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getFirstname',
                    'getLastname',
                    'getStreetLine',
                    'getCity',
                    'getTelephone',
                    'getFax',
                    'getCompany',
                    'getPostcode',
                    'getCountryId',
                ]
            )->getMock();

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

        $addressMock->method('getFirstName')->willReturn($data['firstname']);
        $addressMock->method('getLastname')->willReturn($data['lastname']);
        $addressMock->method('getStreetLine')->with(1)->willReturn($data['street']);
        $addressMock->method('getCity')->willReturn($data['city']);
        $addressMock->method('getTelephone')->willReturn($data['telephone']);
        $addressMock->method('getFax')->willReturn($data['fax']);
        $addressMock->method('getCompany')->willReturn($data['company']);
        $addressMock->method('getPostcode')->willReturn($data['postcode']);
        $addressMock->method('getCountryId')->willReturn($data['country_id']);

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
            'company' => 'Magento',
            'fax' => '222-22-22',
        ];
        $result = [
            'firstname' => [
                array_merge(array_merge($data, ['firstname' => '']), ['country_id' => $countryId++]),
                ['"firstname" is required. Enter and try again.'],
            ],
            'lastname' => [
                array_merge(array_merge($data, ['lastname' => '']), ['country_id' => $countryId++]),
                ['"lastname" is required. Enter and try again.'],
            ],
            'street' => [
                array_merge(array_merge($data, ['street' => '']), ['country_id' => $countryId++]),
                ['"street" is required. Enter and try again.'],
            ],
            'city' => [
                array_merge(array_merge($data, ['city' => '']), ['country_id' => $countryId++]),
                ['"city" is required. Enter and try again.'],
            ],
            'telephone' => [
                array_merge(array_merge($data, ['telephone' => '']), ['country_id' => $countryId++]),
                ['"telephone" is required. Enter and try again.'],
            ],
            'postcode' => [
                array_merge(array_merge($data, ['postcode' => '']), ['country_id' => $countryId++]),
                ['"postcode" is required. Enter and try again.'],
            ],
            'validated' => [array_merge($data, ['country_id' => $countryId++]), []],
        ];

        return $result;
    }
}
