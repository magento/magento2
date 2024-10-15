<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

use Magento\TestFramework\Helper\Bootstrap;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\Country\Postcode\ValidatorInterface
     */
    protected $validator;

    protected function setUp(): void
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
     */
    public function testPostCodesThrowsExceptionIfCountryDoesNotExist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided countryId does not exist.');

        $this->validator->validate('12345', 'INVALID-CODE');
    }

    /**
     * @dataProvider getCanadaInvalidPostCodes
     */
    public function testInvalidCanadaZipCode($countryId, $invalidPostCode)
    {
        $this->assertFalse($this->validator->validate($invalidPostCode, $countryId));
    }

    /**
     * @dataProvider getCanadaValidPostCodes
     */
    public function testValidCanadaZipCode($countryId, $validPostCode)
    {
        $this->assertTrue($this->validator->validate($validPostCode, $countryId));
    }

    /**
     * @return array
     */
    public static function getCanadaInvalidPostCodes()
    {
        return [
            ['countryId' => 'CA', 'invalidPostCode' => '12345'],
            ['countryId' => 'CA', 'invalidPostCode' => 'A1B2C3D'],
            ['countryId' => 'CA', 'invalidPostCode' => 'A1B2C'],
            ['countryId' => 'CA', 'invalidPostCode' => 'A1B  2C3'],
        ];
    }

    /**
     * @return array
     */
    public static function getCanadaValidPostCodes()
    {
        return [
            ['countryId' => 'CA', 'validPostCode' => 'A1B2C3'],
            ['countryId' => 'CA', 'validPostCode' => 'A1B 2C3'],
            ['countryId' => 'CA', 'validPostCode' => 'A1B'],
            ['countryId' => 'CA', 'validPostCode' => 'Z9Y 8X7'],
            ['countryId' => 'CA', 'validPostCode' => 'Z9Y8X7'],
            ['countryId' => 'CA', 'validPostCode' => 'Z9Y'],
        ];
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getPostcodesDataProvider()
    {
        return [
            ['countryId' => 'AD', 'validPostcode' => 'AD100'],
            ['countryId' => 'AM', 'validPostcode' => '123456'],
            ['countryId' => 'AR', 'validPostcode' => '1234'],
            ['countryId' => 'AS', 'validPostcode' => '12345'],
            ['countryId' => 'AT', 'validPostcode' => '1234'],
            ['countryId' => 'AU', 'validPostcode' => '1234'],
            ['countryId' => 'AX', 'validPostcode' => '22101'],
            ['countryId' => 'AZ', 'validPostcode' => '1234'],
            ['countryId' => 'AZ', 'validPostcode' => '123456'],
            ['countryId' => 'BA', 'validPostcode' => '12345'],
            ['countryId' => 'BB', 'validPostcode' => 'BB10900'],
            ['countryId' => 'BD', 'validPostcode' => '1234'],
            ['countryId' => 'BE', 'validPostcode' => '1234'],
            ['countryId' => 'BG', 'validPostcode' => '1234'],
            ['countryId' => 'BH', 'validPostcode' => '323'],
            ['countryId' => 'BH', 'validPostcode' => '1209'],
            ['countryId' => 'BM', 'validPostcode' => 'MA 02'],
            ['countryId' => 'BN', 'validPostcode' => 'PS1234'],
            ['countryId' => 'BR', 'validPostcode' => '12345678'],
            ['countryId' => 'BR', 'validPostcode' => '12345-678'],
            ['countryId' => 'BY', 'validPostcode' => '123456'],
            ['countryId' => 'CA', 'validPostcode' => 'P9M 3T6'],
            ['countryId' => 'CC', 'validPostcode' => '6799'],
            ['countryId' => 'CH', 'validPostcode' => '1234'],
            ['countryId' => 'CK', 'validPostcode' => '1234'],
            ['countryId' => 'CL', 'validPostcode' => '1234567'],
            ['countryId' => 'CN', 'validPostcode' => '123456'],
            ['countryId' => 'CR', 'validPostcode' => '12345'],
            ['countryId' => 'CS', 'validPostcode' => '12345'],
            ['countryId' => 'CU', 'validPostcode' => '12345'],
            ['countryId' => 'CV', 'validPostcode' => '1234'],
            ['countryId' => 'CX', 'validPostcode' => '6798'],
            ['countryId' => 'CY', 'validPostcode' => '1234'],
            ['countryId' => 'CZ', 'validPostcode' => '123 45'],
            ['countryId' => 'DE', 'validPostcode' => '12345'],
            ['countryId' => 'DK', 'validPostcode' => '1234'],
            ['countryId' => 'DO', 'validPostcode' => '12345'],
            ['countryId' => 'DZ', 'validPostcode' => '12345'],
            ['countryId' => 'EC', 'validPostcode' => 'A1234B'],
            ['countryId' => 'EC', 'validPostcode' => 'AB123456'],
            ['countryId' => 'EC', 'validPostcode' => '123456'],
            ['countryId' => 'EE', 'validPostcode' => '12345'],
            ['countryId' => 'EG', 'validPostcode' => '12345'],
            ['countryId' => 'ES', 'validPostcode' => '12345'],
            ['countryId' => 'ET', 'validPostcode' => '1234'],
            ['countryId' => 'FI', 'validPostcode' => '12345'],
            ['countryId' => 'FK', 'validPostcode' => 'FIQQ 1ZZ'],
            ['countryId' => 'FM', 'validPostcode' => '96941'],
            ['countryId' => 'FO', 'validPostcode' => '123'],
            ['countryId' => 'FR', 'validPostcode' => '12345'],
            ['countryId' => 'GB', 'validPostcode' => 'PL12 3RT'],
            ['countryId' => 'GB', 'validPostcode' => 'P1L 2RT'],
            ['countryId' => 'GB', 'validPostcode' => 'QW1 2RT'],
            ['countryId' => 'GB', 'validPostcode' => 'QW1R 2TG'],
            ['countryId' => 'GB', 'validPostcode' => 'L12 3PL'],
            ['countryId' => 'GB', 'validPostcode' => 'Q1 2PL'],
            ['countryId' => 'GE', 'validPostcode' => '1234'],
            ['countryId' => 'GF', 'validPostcode' => '12345'],
            ['countryId' => 'GG', 'validPostcode' => 'GY10 2AB'],
            ['countryId' => 'GL', 'validPostcode' => '1234'],
            ['countryId' => 'GH', 'validPostcode' => 'GA18400'],
            ['countryId' => 'GN', 'validPostcode' => '123'],
            ['countryId' => 'GP', 'validPostcode' => '12345'],
            ['countryId' => 'GR', 'validPostcode' => '12345'],
            ['countryId' => 'GS', 'validPostcode' => 'SIQQ 1ZZ'],
            ['countryId' => 'GT', 'validPostcode' => '12345'],
            ['countryId' => 'GU', 'validPostcode' => '12345'],
            ['countryId' => 'GW', 'validPostcode' => '1234'],
            ['countryId' => 'HM', 'validPostcode' => '1234'],
            ['countryId' => 'HN', 'validPostcode' => '12345'],
            ['countryId' => 'HR', 'validPostcode' => '12345'],
            ['countryId' => 'HT', 'validPostcode' => '1234'],
            ['countryId' => 'HU', 'validPostcode' => '1234'],
            ['countryId' => 'IC', 'validPostcode' => '12345'],
            ['countryId' => 'ID', 'validPostcode' => '12345'],
            ['countryId' => 'IL', 'validPostcode' => '1234567'],
            ['countryId' => 'IM', 'validPostcode' => 'IM1 1AD'],
            ['countryId' => 'IN', 'validPostcode' => '123456'],
            ['countryId' => 'IS', 'validPostcode' => '123'],
            ['countryId' => 'IT', 'validPostcode' => '12345'],
            ['countryId' => 'JE', 'validPostcode' => 'JE2 4PJ'],
            ['countryId' => 'JO', 'validPostcode' => '12345'],
            ['countryId' => 'JP', 'validPostcode' => '123-4567'],
            ['countryId' => 'JP', 'validPostcode' => '1234567'],
            ['countryId' => 'KE', 'validPostcode' => '12345'],
            ['countryId' => 'KG', 'validPostcode' => '123456'],
            ['countryId' => 'KH', 'validPostcode' => '12345'],
            ['countryId' => 'KR', 'validPostcode' => '123-456'],
            ['countryId' => 'KW', 'validPostcode' => '12345'],
            ['countryId' => 'KZ', 'validPostcode' => '123456'],
            ['countryId' => 'LA', 'validPostcode' => '12345'],
            ['countryId' => 'LB', 'validPostcode' => '1234 5678'],
            ['countryId' => 'LI', 'validPostcode' => '1234'],
            ['countryId' => 'LK', 'validPostcode' => '12345'],
            ['countryId' => 'LT', 'validPostcode' => '12345'],
            ['countryId' => 'LU', 'validPostcode' => '1234'],
            ['countryId' => 'LV', 'validPostcode' => '1234'],
            ['countryId' => 'MA', 'validPostcode' => '12345'],
            ['countryId' => 'MC', 'validPostcode' => '12345'],
            ['countryId' => 'ME', 'validPostcode' => '81101'],
            ['countryId' => 'MD', 'validPostcode' => '1234'],
            ['countryId' => 'MG', 'validPostcode' => '123'],
            ['countryId' => 'MH', 'validPostcode' => '12345'],
            ['countryId' => 'MK', 'validPostcode' => '1234'],
            ['countryId' => 'MN', 'validPostcode' => '123456'],
            ['countryId' => 'MP', 'validPostcode' => '12345'],
            ['countryId' => 'MQ', 'validPostcode' => '12345'],
            ['countryId' => 'MS', 'validPostcode' => 'MSR1250'],
            ['countryId' => 'MT', 'validPostcode' => 'WRT 123'],
            ['countryId' => 'MT', 'validPostcode' => 'WRT 45'],
            ['countryId' => 'MU', 'validPostcode' => 'A1201'],
            ['countryId' => 'MU', 'validPostcode' => '80110'],
            ['countryId' => 'MV', 'validPostcode' => '12345'],
            ['countryId' => 'MV', 'validPostcode' => '1234'],
            ['countryId' => 'MX', 'validPostcode' => '12345'],
            ['countryId' => 'MY', 'validPostcode' => '12345'],
            ['countryId' => 'NC', 'validPostcode' => '98800'],
            ['countryId' => 'NE', 'validPostcode' => '1234'],
            ['countryId' => 'NF', 'validPostcode' => '2899'],
            ['countryId' => 'NG', 'validPostcode' => '123456'],
            ['countryId' => 'NI', 'validPostcode' => '22500'],
            ['countryId' => 'NL', 'validPostcode' => '1234 TR'],
            ['countryId' => 'NO', 'validPostcode' => '1234'],
            ['countryId' => 'NP', 'validPostcode' => '12345'],
            ['countryId' => 'NZ', 'validPostcode' => '1234'],
            ['countryId' => 'OM', 'validPostcode' => 'PC 123'],
            ['countryId' => 'PA', 'validPostcode' => '1234'],
            ['countryId' => 'PF', 'validPostcode' => '98701'],
            ['countryId' => 'PG', 'validPostcode' => '123'],
            ['countryId' => 'PH', 'validPostcode' => '1234'],
            ['countryId' => 'PK', 'validPostcode' => '12345'],
            ['countryId' => 'PL', 'validPostcode' => '12-345'],
            ['countryId' => 'PM', 'validPostcode' => '97500'],
            ['countryId' => 'PN', 'validPostcode' => 'PCRN 1ZZ'],
            ['countryId' => 'PR', 'validPostcode' => '12345'],
            ['countryId' => 'PT', 'validPostcode' => '1234'],
            ['countryId' => 'PT', 'validPostcode' => '1234-567'],
            ['countryId' => 'PW', 'validPostcode' => '96939'],
            ['countryId' => 'PW', 'validPostcode' => '96940'],
            ['countryId' => 'PY', 'validPostcode' => '1234'],
            ['countryId' => 'RE', 'validPostcode' => '12345'],
            ['countryId' => 'RO', 'validPostcode' => '123456'],
            ['countryId' => 'RU', 'validPostcode' => '123456'],
            ['countryId' => 'SA', 'validPostcode' => '12345'],
            ['countryId' => 'SE', 'validPostcode' => '123 45'],
            ['countryId' => 'SG', 'validPostcode' => '123456'],
            ['countryId' => 'SH', 'validPostcode' => 'ASCN 1ZZ'],
            ['countryId' => 'SI', 'validPostcode' => '1234'],
            ['countryId' => 'SJ', 'validPostcode' => '1234'],
            ['countryId' => 'SK', 'validPostcode' => '123 45'],
            ['countryId' => 'SM', 'validPostcode' => '47890'],
            ['countryId' => 'SN', 'validPostcode' => '12345'],
            ['countryId' => 'SO', 'validPostcode' => '12345'],
            ['countryId' => 'SZ', 'validPostcode' => 'R123'],
            ['countryId' => 'TC', 'validPostcode' => 'TKCA 1ZZ'],
            ['countryId' => 'TH', 'validPostcode' => '12345'],
            ['countryId' => 'TJ', 'validPostcode' => '123456'],
            ['countryId' => 'TM', 'validPostcode' => '123456'],
            ['countryId' => 'TN', 'validPostcode' => '1234'],
            ['countryId' => 'TR', 'validPostcode' => '12345'],
            ['countryId' => 'TT', 'validPostcode' => '120110'],
            ['countryId' => 'TW', 'validPostcode' => '123'],
            ['countryId' => 'TW', 'validPostcode' => '12345'],
            ['countryId' => 'UA', 'validPostcode' => '02232'],
            ['countryId' => 'US', 'validPostcode' => '12345-6789'],
            ['countryId' => 'US', 'validPostcode' => '12345'],
            ['countryId' => 'UY', 'validPostcode' => '12345'],
            ['countryId' => 'UZ', 'validPostcode' => '123456'],
            ['countryId' => 'VA', 'validPostcode' => '00120'],
            ['countryId' => 'VE', 'validPostcode' => '1234'],
            ['countryId' => 'VI', 'validPostcode' => '12345'],
            ['countryId' => 'WF', 'validPostcode' => '98601'],
            ['countryId' => 'XK', 'validPostcode' => '12345'],
            ['countryId' => 'XY', 'validPostcode' => '12345'],
            ['countryId' => 'YT', 'validPostcode' => '97601'],
            ['countryId' => 'ZA', 'validPostcode' => '1234'],
            ['countryId' => 'ZM', 'validPostcode' => '12345'],
        ];
    }
}
