<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Directory\Model\Country\Postcode;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;

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
     */
    #[DataProvider('getPostcodesDataProvider')]
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
     */
    #[DataProvider('getCanadaInvalidPostCodes')]
    public function testInvalidCanadaZipCode($countryId, $invalidPostCode)
    {
        $this->assertFalse($this->validator->validate($invalidPostCode, $countryId));
    }

    /**
     */
    #[DataProvider('getCanadaValidPostCodes')]
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
            ['CA', '12345'],  // $countryId, $invalidPostCode
            ['CA', 'A1B2C3D'],
            ['CA', 'A1B2C'],
            ['CA', 'A1B  2C3'],
        ];
    }

    /**
     * @return array
     */
    public static function getCanadaValidPostCodes()
    {
        return [
            ['CA', 'A1B2C3'],  // $countryId, $validPostCode
            ['CA', 'A1B 2C3'],
            ['CA', 'A1B'],
            ['CA', 'Z9Y 8X7'],
            ['CA', 'Z9Y8X7'],
            ['CA', 'Z9Y'],
        ];
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getPostcodesDataProvider()
    {
        return [
            ['AD', 'AD100'],  // $countryId, $validPostcode
            ['AM', '123456'],
            ['AR', '1234'], ['AS', '12345'], ['AT', '1234'], ['AU', '1234'], ['AX', '22101'],
            ['AZ', '1234'], ['AZ', '123456'], ['BA', '12345'], ['BB', 'BB10900'], ['BD', '1234'],
            ['BE', '1234'], ['BG', '1234'], ['BH', '323'], ['BH', '1209'], ['BM', 'MA 02'],
            ['BN', 'PS1234'], ['BR', '12345678'], ['BR', '12345-678'], ['BY', '123456'],
            ['CA', 'P9M 3T6'], ['CC', '6799'], ['CH', '1234'], ['CK', '1234'], ['CL', '1234567'],
            ['CN', '123456'], ['CR', '12345'], ['CS', '12345'], ['CU', '12345'], ['CV', '1234'],
            ['CX', '6798'], ['CY', '1234'], ['CZ', '123 45'], ['DE', '12345'], ['DK', '1234'],
            ['DO', '12345'], ['DZ', '12345'], ['EC', 'A1234B'], ['EC', 'AB123456'], ['EC', '123456'],
            ['EE', '12345'], ['EG', '12345'], ['ES', '12345'], ['ET', '1234'], ['FI', '12345'],
            ['FK', 'FIQQ 1ZZ'], ['FM', '96941'], ['FO', '123'], ['FR', '12345'],
            ['GB', 'PL12 3RT'], ['GB', 'P1L 2RT'], ['GB', 'QW1 2RT'], ['GB', 'QW1R 2TG'],
            ['GB', 'L12 3PL'], ['GB', 'Q1 2PL'], ['GE', '1234'], ['GF', '12345'],
            ['GG', 'GY10 2AB'], ['GL', '1234'], ['GH', 'GA18400'], ['GN', '123'], ['GP', '12345'],
            ['GR', '12345'], ['GS', 'SIQQ 1ZZ'], ['GT', '12345'], ['GU', '12345'], ['GW', '1234'],
            ['HM', '1234'], ['HN', '12345'], ['HR', '12345'], ['HT', '1234'], ['HU', '1234'],
            ['IC', '12345'], ['ID', '12345'], ['IR', 'A65 F4E2'], ['IR', 'D02 X285'],
            ['IL', '1234567'], ['IM', 'IM1 1AD'], ['IN', '123456'], ['IS', '123'], ['IT', '12345'],
            ['JE', 'JE2 4PJ'], ['JO', '12345'], ['JP', '123-4567'], ['JP', '1234567'],
            ['KE', '12345'], ['KG', '123456'], ['KH', '12345'], ['KR', '123-456'], ['KW', '12345'],
            ['KZ', '123456'], ['LA', '12345'], ['LB', '1234 5678'], ['LI', '1234'], ['LK', '12345'],
            ['LT', '12345'], ['LU', '1234'], ['LV', '1234'], ['MA', '12345'], ['MC', '12345'],
            ['ME', '81101'], ['MD', '1234'], ['MG', '123'], ['MH', '12345'], ['MK', '1234'],
            ['MN', '123456'], ['MP', '12345'], ['MQ', '12345'], ['MS', 'MSR1250'],
            ['MT', 'WRT 123'], ['MT', 'WRT 45'], ['MU', 'A1201'], ['MU', '80110'],
            ['MV', '12345'], ['MV', '1234'], ['MX', '12345'], ['MY', '12345'], ['NC', '98800'],
            ['NE', '1234'], ['NF', '2899'], ['NG', '123456'], ['NI', '22500'], ['NL', '1234 TR'],
            ['NO', '1234'], ['NP', '12345'], ['NZ', '1234'], ['OM', 'PC 123'], ['PA', '1234'],
            ['PF', '98701'], ['PG', '123'], ['PH', '1234'], ['PK', '12345'], ['PL', '12-345'],
            ['PM', '97500'], ['PN', 'PCRN 1ZZ'], ['PR', '12345'], ['PT', '1234'], ['PT', '1234-567'],
            ['PW', '96939'], ['PW', '96940'], ['PY', '1234'], ['RE', '12345'], ['RO', '123456'],
            ['RU', '123456'], ['SA', '12345'], ['SE', '123 45'], ['SG', '123456'],
            ['SH', 'ASCN 1ZZ'], ['SI', '1234'], ['SJ', '1234'], ['SK', '123 45'], ['SM', '47890'],
            ['SN', '12345'], ['SO', '12345'], ['SZ', 'R123'], ['TC', 'TKCA 1ZZ'], ['TH', '12345'],
            ['TJ', '123456'], ['TM', '123456'], ['TN', '1234'], ['TR', '12345'], ['TT', '120110'],
            ['TW', '123'], ['TW', '12345'], ['UA', '02232'], ['US', '12345-6789'], ['US', '12345'],
            ['UY', '12345'], ['UZ', '123456'], ['VA', '00120'], ['VE', '1234'], ['VI', '12345'],
            ['WF', '98601'], ['XK', '12345'], ['XY', '12345'], ['YT', '97601'], ['ZA', '1234'],
            ['ZM', '12345'],
        ];
    }
}
