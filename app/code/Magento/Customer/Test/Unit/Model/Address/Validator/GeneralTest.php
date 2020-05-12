<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address\Validator;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\Validator\General;
use Magento\Directory\Helper\Data;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Magento\Customer\Model\Address\Validator\General tests.
 */
class GeneralTest extends TestCase
{
    /** @var Data|MockObject  */
    private $directoryDataMock;

    /** @var Config|MockObject  */
    private $eavConfigMock;

    /** @var General  */
    private $model;

    /** @var ObjectManager */
    private $objectManager;

    protected function setUp(): void
    {
        $this->directoryDataMock = $this->createMock(Data::class);
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            General::class,
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
            ->getMockBuilder(AbstractAddress::class)
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

        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())
            ->method('getIsRequired')
            ->willReturn(true);

        $this->eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $this->directoryDataMock->expects($this->once())
            ->method('getCountriesWithOptionalZip')
            ->willReturn([]);

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
