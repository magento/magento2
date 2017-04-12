<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $this->validator = $objectManager->create(\Magento\Directory\Model\Country\Postcode\ValidatorInterface::class);
    }

    /**
     * @dataProvider getPostcodesDataProvider
     */
    public function testPostCodes($countryId, $validPostcode)
    {
        try {
            $this->assertTrue($this->validator->validate($validPostcode, $countryId));
            $this->assertFalse($this->validator->validate('INVALID-100', $countryId));
        } catch (\InvalidArgumentException $ex) {
            //skip validation test for none existing countryId
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Provided countryId does not exist.
     */
    public function testPostCodesThrowsExceptionIfCountryDoesNotExist()
    {
        $this->validator->validate('12345', 'INVALID-CODE');
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getPostcodesDataProvider()
    {
        return [
            ['countryId' => 'DZ', 'postcode' => '12345'],
            ['countryId' => 'AS', 'postcode' => '12345'],
            ['countryId' => 'AR', 'postcode' => '1234'],
            ['countryId' => 'AM', 'postcode' => '123456'],
            ['countryId' => 'AU', 'postcode' => '1234'],
            ['countryId' => 'AT', 'postcode' => '1234'],
            ['countryId' => 'AZ', 'postcode' => '1234'],
            ['countryId' => 'AZ', 'postcode' => '123456'],
            ['countryId' => 'BD', 'postcode' => '1234'],
            ['countryId' => 'BY', 'postcode' => '123456'],
            ['countryId' => 'BE', 'postcode' => '1234'],
            ['countryId' => 'BA', 'postcode' => '12345'],
            ['countryId' => 'BR', 'postcode' => '12345'],
            ['countryId' => 'BR', 'postcode' => '12345-678'],
            ['countryId' => 'BN', 'postcode' => 'PS1234'],
            ['countryId' => 'BG', 'postcode' => '1234'],
            ['countryId' => 'CA', 'postcode' => 'P9M 3T6'],
            ['countryId' => 'IC', 'postcode' => '12345'],
            ['countryId' => 'CN', 'postcode' => '123456'],
            ['countryId' => 'HR', 'postcode' => '12345'],
            ['countryId' => 'CU', 'postcode' => '12345'],
            ['countryId' => 'CY', 'postcode' => '1234'],
            ['countryId' => 'CZ', 'postcode' => '123 45'],
            ['countryId' => 'DK', 'postcode' => '1234'],
            ['countryId' => 'EE', 'postcode' => '12345'],
            ['countryId' => 'FI', 'postcode' => '12345'],
            ['countryId' => 'FR', 'postcode' => '12345'],
            ['countryId' => 'GF', 'postcode' => '12345'],
            ['countryId' => 'GE', 'postcode' => '1234'],
            ['countryId' => 'DE', 'postcode' => '12345'],
            ['countryId' => 'GR', 'postcode' => '123 45'],
            ['countryId' => 'GL', 'postcode' => '1234'],
            ['countryId' => 'GP', 'postcode' => '12345'],
            ['countryId' => 'GU', 'postcode' => '12345'],
            ['countryId' => 'GG', 'postcode' => 'PL5 7TH'],
            ['countryId' => 'HU', 'postcode' => '1234'],
            ['countryId' => 'IS', 'postcode' => '123'],
            ['countryId' => 'IN', 'postcode' => '123456'],
            ['countryId' => 'ID', 'postcode' => '12345'],
            ['countryId' => 'IL', 'postcode' => '12345'],
            ['countryId' => 'IT', 'postcode' => '12345'],
            ['countryId' => 'JP', 'postcode' => '123-4567'],
            ['countryId' => 'JP', 'postcode' => '123'],
            ['countryId' => 'JE', 'postcode' => 'TY8 9PL'],
            ['countryId' => 'KZ', 'postcode' => '123456'],
            ['countryId' => 'KE', 'postcode' => '12345'],
            ['countryId' => 'KR', 'postcode' => '123-456'],
            ['countryId' => 'KG', 'postcode' => '123456'],
            ['countryId' => 'LV', 'postcode' => '1234'],
            ['countryId' => 'LI', 'postcode' => '1234'],
            ['countryId' => 'LT', 'postcode' => '12345'],
            ['countryId' => 'LU', 'postcode' => '1234'],
            ['countryId' => 'MK', 'postcode' => '1234'],
            ['countryId' => 'MG', 'postcode' => '123'],
            ['countryId' => 'MY', 'postcode' => '12345'],
            ['countryId' => 'MV', 'postcode' => '12345'],
            ['countryId' => 'MV', 'postcode' => '1234'],
            ['countryId' => 'MT', 'postcode' => 'WRT 123'],
            ['countryId' => 'MT', 'postcode' => 'WRT 45'],
            ['countryId' => 'MH', 'postcode' => '12345'],
            ['countryId' => 'MQ', 'postcode' => '12345'],
            ['countryId' => 'MX', 'postcode' => '12345'],
            ['countryId' => 'MD', 'postcode' => '1234'],
            ['countryId' => 'MC', 'postcode' => '12345'],
            ['countryId' => 'MN', 'postcode' => '123456'],
            ['countryId' => 'MA', 'postcode' => '12345'],
            ['countryId' => 'NL', 'postcode' => '1234 TR'],
            ['countryId' => 'NO', 'postcode' => '1234'],
            ['countryId' => 'PK', 'postcode' => '12345'],
            ['countryId' => 'PH', 'postcode' => '1234'],
            ['countryId' => 'PL', 'postcode' => '12-345'],
            ['countryId' => 'PT', 'postcode' => '1234'],
            ['countryId' => 'PT', 'postcode' => '1234-567'],
            ['countryId' => 'PR', 'postcode' => '12345'],
            ['countryId' => 'RE', 'postcode' => '12345'],
            ['countryId' => 'RO', 'postcode' => '123456'],
            ['countryId' => 'RU', 'postcode' => '123456'],
            ['countryId' => 'MP', 'postcode' => '12345'],
            ['countryId' => 'CS', 'postcode' => '12345'],
            ['countryId' => 'SG', 'postcode' => '123456'],
            ['countryId' => 'SK', 'postcode' => '123 45'],
            ['countryId' => 'SI', 'postcode' => '1234'],
            ['countryId' => 'ZA', 'postcode' => '1234'],
            ['countryId' => 'ES', 'postcode' => '12345'],
            ['countryId' => 'XY', 'postcode' => '12345'],
            ['countryId' => 'SZ', 'postcode' => 'R123'],
            ['countryId' => 'SE', 'postcode' => '123 45'],
            ['countryId' => 'CH', 'postcode' => '1234'],
            ['countryId' => 'TW', 'postcode' => '123'],
            ['countryId' => 'TW', 'postcode' => '12345'],
            ['countryId' => 'TJ', 'postcode' => '123456'],
            ['countryId' => 'TH', 'postcode' => '12345'],
            ['countryId' => 'TR', 'postcode' => '12345'],
            ['countryId' => 'TM', 'postcode' => '123456'],
            ['countryId' => 'UA', 'postcode' => '12345'],
            ['countryId' => 'GB', 'postcode' => 'PL12 3RT'],
            ['countryId' => 'GB', 'postcode' => 'P1L 2RT'],
            ['countryId' => 'GB', 'postcode' => 'QW1 2RT'],
            ['countryId' => 'GB', 'postcode' => 'QW1R 2TG'],
            ['countryId' => 'GB', 'postcode' => 'L12 3PL'],
            ['countryId' => 'GB', 'postcode' => 'Q1 2PL'],
            ['countryId' => 'US', 'postcode' => '12345-6789'],
            ['countryId' => 'US', 'postcode' => '12345'],
            ['countryId' => 'UY', 'postcode' => '12345'],
            ['countryId' => 'UZ', 'postcode' => '123456'],
            ['countryId' => 'VI', 'postcode' => '12345'],
        ];
    }
}
