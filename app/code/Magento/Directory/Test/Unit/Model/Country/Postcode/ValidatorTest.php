<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Country\Postcode;

use Magento\Directory\Model\Country\Postcode\Config;
use Magento\Directory\Model\Country\Postcode\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $postcodesConfigMock;

    /**
     * @var Validator
     */
    protected $model;

    protected function setUp(): void
    {
        $this->postcodesConfigMock = $this->createMock(Config::class);
        $postCodes = [
            'US' => [
                'pattern_1' => ['pattern' => '^[0-9]{5}\-[0-9]{4}$'],
                'pattern_2' => ['pattern' => '^[0-9]{5}$']
            ],
            'NL' => [
                'pattern_1' => ['pattern' => '^[1-9][0-9]{3}\s?[a-zA-Z]{2}$'],
                'pattern_2' => ['pattern' => '^[1-9][0-9]{3}$']
            ]
        ];
        $this->postcodesConfigMock->expects($this->once())->method('getPostCodes')->willReturn($postCodes);
        $this->model = new Validator($this->postcodesConfigMock);
    }

    /**
     * @param string $postCode
     * @param string $countryId
     * @return void
     * @dataProvider getCountryPostcodes
     */
    public function testValidatePositive(string $postCode, string $countryId): void
    {
        $this->assertTrue($this->model->validate($postCode, $countryId));
    }

    public function testValidateNegative()
    {
        $postcode = 'POST-CODE';
        $countryId = 'US';
        $this->assertFalse($this->model->validate($postcode, $countryId));
    }

    public function testValidateThrowExceptionIfCountryDoesNotExist()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Provided countryId does not exist.');
        $postcode = '12345-6789';
        $countryId = 'QQ';
        $this->assertFalse($this->model->validate($postcode, $countryId));
    }

    /**
     * @return \string[][]
     */
    public static function getCountryPostcodes(): array
    {
        return [
            [
                'postCode' => '12345-6789',
                'countryId' => 'US'
            ],
            [
                'postCode' => '1234',
                'countryId' => 'NL'
            ],
            [
                'postCode' => '1234AB',
                'countryId' => 'NL'
            ]
        ];
    }
}
