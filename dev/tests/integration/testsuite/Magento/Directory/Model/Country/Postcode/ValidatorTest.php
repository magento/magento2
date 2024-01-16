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
    public function getCanadaInvalidPostCodes()
    {
        return [
            ['countryId' => 'CA', 'postcode' => '12345'],
            ['countryId' => 'CA', 'postcode' => 'A1B2C3D'],
            ['countryId' => 'CA', 'postcode' => 'A1B2C'],
            ['countryId' => 'CA', 'postcode' => 'A1B  2C3'],
        ];
    }

    /**
     * @return array
     */
    public function getCanadaValidPostCodes()
    {
        return [
            ['countryId' => 'CA', 'postcode' => 'A1B2C3'],
            ['countryId' => 'CA', 'postcode' => 'A1B 2C3'],
            ['countryId' => 'CA', 'postcode' => 'A1B'],
            ['countryId' => 'CA', 'postcode' => 'Z9Y 8X7'],
            ['countryId' => 'CA', 'postcode' => 'Z9Y8X7'],
            ['countryId' => 'CA', 'postcode' => 'Z9Y'],
        ];
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getPostcodesDataProvider()
    {
        return [
            ['countryId' => 'AD', 'postcode' => 'AD100'],
            ['countryId' => 'AM', 'postcode' => '123456'],
            ['countryId' => 'AR', 'postcode' => '1234'],
            ['countryId' => 'AS', 'postcode' => '12345'],
            ['countryId' => 'AT', 'postcode' => '1234'],
            ['countryId' => 'AU', 'postcode' => '1234'],
            ['countryId' => 'AX', 'postcode' => '22101'],
            ['countryId' => 'AZ', 'postcode' => '1234'],
            ['countryId' => 'AZ', 'postcode' => '123456'],
            ['countryId' => 'BA', 'postcode' => '12345'],
            ['countryId' => 'BB', 'postcode' => 'BB10900'],
            ['countryId' => 'BD', 'postcode' => '1234'],
            ['countryId' => 'BE', 'postcode' => '1234'],
            ['countryId' => 'BG', 'postcode' => '1234'],
            ['countryId' => 'BH', 'postcode' => '323'],
            ['countryId' => 'BH', 'postcode' => '1209'],
            ['countryId' => 'BM', 'postcode' => 'MA 02'],
            ['countryId' => 'BN', 'postcode' => 'PS1234'],
            ['countryId' => 'BR', 'postcode' => '12345678'],
            ['countryId' => 'BR', 'postcode' => '12345-678'],
            ['countryId' => 'BY', 'postcode' => '123456'],
            ['countryId' => 'CA', 'postcode' => 'P9M 3T6'],
            ['countryId' => 'CC', 'postcode' => '6799'],
            ['countryId' => 'CH', 'postcode' => '1234'],
            ['countryId' => 'CK', 'postcode' => '1234'],
            ['countryId' => 'CL', 'postcode' => '1234567'],
            ['countryId' => 'CN', 'postcode' => '123456'],
            ['countryId' => 'CR', 'postcode' => '12345'],
            ['countryId' => 'CS', 'postcode' => '12345'],
            ['countryId' => 'CU', 'postcode' => '12345'],
            ['countryId' => 'CV', 'postcode' => '1234'],
            ['countryId' => 'CX', 'postcode' => '6798'],
            ['countryId' => 'CY', 'postcode' => '1234'],
            ['countryId' => 'CZ', 'postcode' => '123 45'],
            ['countryId' => 'DE', 'postcode' => '12345'],
            ['countryId' => 'DK', 'postcode' => '1234'],
            ['countryId' => 'DO', 'postcode' => '12345'],
            ['countryId' => 'DZ', 'postcode' => '12345'],
            ['countryId' => 'EC', 'postcode' => 'A1234B'],
            ['countryId' => 'EC', 'postcode' => 'AB123456'],
            ['countryId' => 'EC', 'postcode' => '123456'],
            ['countryId' => 'EE', 'postcode' => '12345'],
            ['countryId' => 'EG', 'postcode' => '12345'],
            ['countryId' => 'ES', 'postcode' => '12345'],
            ['countryId' => 'ET', 'postcode' => '1234'],
            ['countryId' => 'FI', 'postcode' => '12345'],
            ['countryId' => 'FK', 'postcode' => 'FIQQ 1ZZ'],
            ['countryId' => 'FM', 'postcode' => '96941'],
            ['countryId' => 'FO', 'postcode' => '123'],
            ['countryId' => 'FR', 'postcode' => '12345'],
            ['countryId' => 'GB', 'postcode' => 'PL12 3RT'],
            ['countryId' => 'GB', 'postcode' => 'P1L 2RT'],
            ['countryId' => 'GB', 'postcode' => 'QW1 2RT'],
            ['countryId' => 'GB', 'postcode' => 'QW1R 2TG'],
            ['countryId' => 'GB', 'postcode' => 'L12 3PL'],
            ['countryId' => 'GB', 'postcode' => 'Q1 2PL'],
            ['countryId' => 'GE', 'postcode' => '1234'],
            ['countryId' => 'GF', 'postcode' => '12345'],
            ['countryId' => 'GG', 'postcode' => 'GY10 2AB'],
            ['countryId' => 'GL', 'postcode' => '1234'],
            ['countryId' => 'GH', 'postcode' => 'GA18400'],
            ['countryId' => 'GN', 'postcode' => '123'],
            ['countryId' => 'GP', 'postcode' => '12345'],
            ['countryId' => 'GR', 'postcode' => '12345'],
            ['countryId' => 'GS', 'postcode' => 'SIQQ 1ZZ'],
            ['countryId' => 'GT', 'postcode' => '12345'],
            ['countryId' => 'GU', 'postcode' => '12345'],
            ['countryId' => 'GW', 'postcode' => '1234'],
            ['countryId' => 'HM', 'postcode' => '1234'],
            ['countryId' => 'HN', 'postcode' => '12345'],
            ['countryId' => 'HR', 'postcode' => '12345'],
            ['countryId' => 'HT', 'postcode' => '1234'],
            ['countryId' => 'HU', 'postcode' => '1234'],
            ['countryId' => 'IC', 'postcode' => '12345'],
            ['countryId' => 'ID', 'postcode' => '12345'],
            ['countryId' => 'IL', 'postcode' => '1234567'],
            ['countryId' => 'IM', 'postcode' => 'IM1 1AD'],
            ['countryId' => 'IN', 'postcode' => '123456'],
            ['countryId' => 'IS', 'postcode' => '123'],
            ['countryId' => 'IT', 'postcode' => '12345'],
            ['countryId' => 'JE', 'postcode' => 'JE2 4PJ'],
            ['countryId' => 'JO', 'postcode' => '12345'],
            ['countryId' => 'JP', 'postcode' => '123-4567'],
            ['countryId' => 'JP', 'postcode' => '1234567'],
            ['countryId' => 'KE', 'postcode' => '12345'],
            ['countryId' => 'KG', 'postcode' => '123456'],
            ['countryId' => 'KH', 'postcode' => '12345'],
            ['countryId' => 'KR', 'postcode' => '123-456'],
            ['countryId' => 'KW', 'postcode' => '12345'],
            ['countryId' => 'KZ', 'postcode' => '123456'],
            ['countryId' => 'LA', 'postcode' => '12345'],
            ['countryId' => 'LB', 'postcode' => '1234 5678'],
            ['countryId' => 'LI', 'postcode' => '1234'],
            ['countryId' => 'LK', 'postcode' => '12345'],
            ['countryId' => 'LT', 'postcode' => '12345'],
            ['countryId' => 'LU', 'postcode' => '1234'],
            ['countryId' => 'LV', 'postcode' => '1234'],
            ['countryId' => 'MA', 'postcode' => '12345'],
            ['countryId' => 'MC', 'postcode' => '12345'],
            ['countryId' => 'ME', 'postcode' => '81101'],
            ['countryId' => 'MD', 'postcode' => '1234'],
            ['countryId' => 'MG', 'postcode' => '123'],
            ['countryId' => 'MH', 'postcode' => '12345'],
            ['countryId' => 'MK', 'postcode' => '1234'],
            ['countryId' => 'MN', 'postcode' => '123456'],
            ['countryId' => 'MP', 'postcode' => '12345'],
            ['countryId' => 'MQ', 'postcode' => '12345'],
            ['countryId' => 'MS', 'postcode' => 'MSR1250'],
            ['countryId' => 'MT', 'postcode' => 'WRT 123'],
            ['countryId' => 'MT', 'postcode' => 'WRT 45'],
            ['countryId' => 'MU', 'postcode' => 'A1201'],
            ['countryId' => 'MU', 'postcode' => '80110'],
            ['countryId' => 'MV', 'postcode' => '12345'],
            ['countryId' => 'MV', 'postcode' => '1234'],
            ['countryId' => 'MX', 'postcode' => '12345'],
            ['countryId' => 'MY', 'postcode' => '12345'],
            ['countryId' => 'NC', 'postcode' => '98800'],
            ['countryId' => 'NE', 'postcode' => '1234'],
            ['countryId' => 'NF', 'postcode' => '2899'],
            ['countryId' => 'NG', 'postcode' => '123456'],
            ['countryId' => 'NI', 'postcode' => '22500'],
            ['countryId' => 'NL', 'postcode' => '1234 TR'],
            ['countryId' => 'NO', 'postcode' => '1234'],
            ['countryId' => 'NP', 'postcode' => '12345'],
            ['countryId' => 'NZ', 'postcode' => '1234'],
            ['countryId' => 'OM', 'postcode' => 'PC 123'],
            ['countryId' => 'PA', 'postcode' => '1234'],
            ['countryId' => 'PF', 'postcode' => '98701'],
            ['countryId' => 'PG', 'postcode' => '123'],
            ['countryId' => 'PH', 'postcode' => '1234'],
            ['countryId' => 'PK', 'postcode' => '12345'],
            ['countryId' => 'PL', 'postcode' => '12-345'],
            ['countryId' => 'PM', 'postcode' => '97500'],
            ['countryId' => 'PN', 'postcode' => 'PCRN 1ZZ'],
            ['countryId' => 'PR', 'postcode' => '12345'],
            ['countryId' => 'PT', 'postcode' => '1234'],
            ['countryId' => 'PT', 'postcode' => '1234-567'],
            ['countryId' => 'PW', 'postcode' => '96939'],
            ['countryId' => 'PW', 'postcode' => '96940'],
            ['countryId' => 'PY', 'postcode' => '1234'],
            ['countryId' => 'RE', 'postcode' => '12345'],
            ['countryId' => 'RO', 'postcode' => '123456'],
            ['countryId' => 'RU', 'postcode' => '123456'],
            ['countryId' => 'SA', 'postcode' => '12345'],
            ['countryId' => 'SE', 'postcode' => '123 45'],
            ['countryId' => 'SG', 'postcode' => '123456'],
            ['countryId' => 'SH', 'postcode' => 'ASCN 1ZZ'],
            ['countryId' => 'SI', 'postcode' => '1234'],
            ['countryId' => 'SJ', 'postcode' => '1234'],
            ['countryId' => 'SK', 'postcode' => '123 45'],
            ['countryId' => 'SM', 'postcode' => '47890'],
            ['countryId' => 'SN', 'postcode' => '12345'],
            ['countryId' => 'SO', 'postcode' => '12345'],
            ['countryId' => 'SZ', 'postcode' => 'R123'],
            ['countryId' => 'TC', 'postcode' => 'TKCA 1ZZ'],
            ['countryId' => 'TH', 'postcode' => '12345'],
            ['countryId' => 'TJ', 'postcode' => '123456'],
            ['countryId' => 'TM', 'postcode' => '123456'],
            ['countryId' => 'TN', 'postcode' => '1234'],
            ['countryId' => 'TR', 'postcode' => '12345'],
            ['countryId' => 'TT', 'postcode' => '120110'],
            ['countryId' => 'TW', 'postcode' => '123'],
            ['countryId' => 'TW', 'postcode' => '12345'],
            ['countryId' => 'UA', 'postcode' => '02232'],
            ['countryId' => 'US', 'postcode' => '12345-6789'],
            ['countryId' => 'US', 'postcode' => '12345'],
            ['countryId' => 'UY', 'postcode' => '12345'],
            ['countryId' => 'UZ', 'postcode' => '123456'],
            ['countryId' => 'VA', 'postcode' => '00120'],
            ['countryId' => 'VE', 'postcode' => '1234'],
            ['countryId' => 'VI', 'postcode' => '12345'],
            ['countryId' => 'WF', 'postcode' => '98601'],
            ['countryId' => 'XK', 'postcode' => '12345'],
            ['countryId' => 'XY', 'postcode' => '12345'],
            ['countryId' => 'YT', 'postcode' => '97601'],
            ['countryId' => 'ZA', 'postcode' => '1234'],
            ['countryId' => 'ZM', 'postcode' => '12345'],
        ];
    }
}
