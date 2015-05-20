<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

use Magento\TestFramework\Helper\Bootstrap;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Country\Postcode\ValidatorInterface
     */
    protected $validator;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->validator = $objectManager->create('Magento\Directory\Model\Country\Postcode\ValidatorInterface');
    }

    /**
     * @dataProvider getPostcodesDataProvider
     */
    public function testValidPostCodes($countryId, $validPostcode, $invalidPostcode)
    {
        $this->assertTrue($this->validator->validate($validPostcode, $countryId));
        $this->assertFalse($this->validator->validate($invalidPostcode, $countryId));
    }

    /**
     * @return array
     */
    public function getPostcodesDataProvider()
    {
        return [
            ['countryId' => 'DZ', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'AS', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'AR', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'AM', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'AU', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'AT', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'AZ', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'AZ', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'BD', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'BY', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'BE', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'BA', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'BR', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'BR', 'validPostcode' => '12345-678', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'BN', 'validPostcode' => 'PS1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'BG', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'CA', 'validPostcode' => 'P9M 3T6', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'IC', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'CN', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'HR', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'CU', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'CY', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'CZ', 'validPostcode' => '123 45', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'DK', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'EE', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'FI', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'FR', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GF', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GE', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'DE', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GR', 'validPostcode' => '123 45', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GL', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GP', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GU', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GG', 'validPostcode' => 'PL5 7TH', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'HU', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'IS', 'validPostcode' => '123', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'IN', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'ID', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'IL', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'IT', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'JP', 'validPostcode' => '123-4567', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'JP', 'validPostcode' => '123', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'JE', 'validPostcode' => 'TY8 9PL', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'KZ', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'KE', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'KR', 'validPostcode' => '123-456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'KG', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'LV', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'LI', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'LT', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'LU', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MK', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MG', 'validPostcode' => '123', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MY', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MV', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MV', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MT', 'validPostcode' => 'WRT 123', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MT', 'validPostcode' => 'WRT 45', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MH', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MQ', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MX', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MD', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MC', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MN', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MA', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'NL', 'validPostcode' => '1234 TR', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'NO', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'PK', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'PH', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'PL', 'validPostcode' => '12-345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'PT', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'PT', 'validPostcode' => '1234-567', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'PR', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'RE', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'RO', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'RU', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'MP', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'CS', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'SG', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'SK', 'validPostcode' => '123 45', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'SI', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'ZA', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'ES', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'XY', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'SZ', 'validPostcode' => 'R123', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'SE', 'validPostcode' => '123 45', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'CH', 'validPostcode' => '1234', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'TW', 'validPostcode' => '123', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'TW', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'TJ', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'TH', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'TR', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'TM', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'UA', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GB', 'validPostcode' => 'PL12 3RT', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GB', 'validPostcode' => 'P1L 2RT', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GB', 'validPostcode' => 'QW1 2RT', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GB', 'validPostcode' => 'QW1R 2TG', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GB', 'validPostcode' => 'L12 3PL', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'GB', 'validPostcode' => 'Q1 2PL', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'US', 'validPostcode' => '12345-6789', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'US', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'UY', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'UZ', 'validPostcode' => '123456', 'invalidPostcode' => 'INVALID-100'],
            ['countryId' => 'VI', 'validPostcode' => '12345', 'invalidPostcode' => 'INVALID-100'],
        ];
    }
}
